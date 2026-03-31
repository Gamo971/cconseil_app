<?php

namespace App\Services;

use App\Models\FinancialData;
use App\Models\Kpi;
use App\Models\Client;

/**
 * Moteur de calcul des indicateurs clés (KPIs)
 *
 * Formules utilisées :
 *   Marge brute     = CA - Achats marchandises - Autres achats
 *   Taux marge brute = Marge brute / CA * 100
 *   Seuil rentabilité = Charges fixes / Taux marge brute
 *   EBE              = CA - Charges variables - Masse salariale totale - Amortissements (approx)
 *   Taux EBE         = EBE / CA * 100
 *   CAF              = EBE (simplifiée, sans résultat financier ni IS)
 *   Trésorerie nette = Trésorerie fin de période
 *   Jours tréso      = Trésorerie nette / (CA / 365)
 *   CA/salarié        = CA / Nombre de salariés
 */
class KpiCalculatorService
{
    /**
     * Calcule tous les KPIs depuis des données financières
     * et persiste le résultat en base.
     */
    public function calculateAndSave(FinancialData $fd): Kpi
    {
        $data = $this->calculate($fd);

        return Kpi::updateOrCreate(
            [
                'client_id'        => $fd->client_id,
                'annee'            => $fd->annee,
                'mois'             => $fd->mois,
            ],
            array_merge($data, ['financial_data_id' => $fd->id])
        );
    }

    /**
     * Calcule les KPIs sans persister — utile pour preview
     */
    public function calculate(FinancialData $fd): array
    {
        $ca                = (float)($fd->ca ?? 0);
        $achats            = (float)($fd->achats_marchandises ?? 0) + (float)($fd->autres_achats ?? 0);
        $chargesFixes      = (float)($fd->charges_fixes ?? 0);
        $chargesVariables  = (float)($fd->charges_variables ?? 0);
        $masseSal          = (float)($fd->masse_salariale_brute ?? 0);
        $chargesPat        = (float)($fd->charges_patronales ?? 0);
        $amortissements    = (float)($fd->amortissements ?? 0);
        $tresorieFin       = (float)($fd->tresorerie_fin ?? 0);
        $nbSalaries        = (int)($fd->nombre_salaries ?? 0);

        // ── Marge brute ──────────────────────────────────────────────────────
        $margeBrute     = $ca - $achats;
        $tauxMargeBrute = $ca > 0 ? ($margeBrute / $ca) * 100 : null;

        // ── Seuil de rentabilité ─────────────────────────────────────────────
        // Seuil = Charges fixes / Taux de marge brute
        $seuilRentabilite = null;
        if ($tauxMargeBrute && $tauxMargeBrute > 0) {
            $seuilRentabilite = $chargesFixes / ($tauxMargeBrute / 100);
        }

        // ── EBE (Excédent Brut d'Exploitation) ──────────────────────────────
        // EBE = CA - Charges variables - Masse salariale totale - Charges fixes (hors amort.)
        $masseSalarialeTotal = $masseSal + $chargesPat;
        $ebe = $ca - $chargesVariables - $masseSalarialeTotal - $chargesFixes + $amortissements;
        $tauxEbe = $ca > 0 ? ($ebe / $ca) * 100 : null;

        // ── CAF (Capacité d'autofinancement) ─────────────────────────────────
        // Simplification : CAF ≈ EBE (sans résultat financier, sans IS)
        $caf = $ebe;

        // ── Trésorerie ───────────────────────────────────────────────────────
        $tresorerieNette = $tresorieFin;
        $joursTresorerie = null;
        if ($ca > 0 && $tresorerieNette !== null) {
            $caMoyen = $ca / ($fd->mois ? 1 : 12); // si mensuel = /1, si annuel = /12
            $caJournalier = $caMoyen / 30;
            $joursTresorerie = $caJournalier > 0
                ? (int)round($tresorerieNette / $caJournalier)
                : null;
        }

        // ── Productivité salariale ────────────────────────────────────────────
        $caParSalarie = null;
        $productivite = null;
        if ($nbSalaries > 0) {
            $caParSalarie = $ca / $nbSalaries;
            if ($masseSalarialeTotal > 0) {
                // Valeur ajoutée approximative = Marge brute - Charges fixes
                $valeurAjoutee = $margeBrute - $chargesFixes;
                $productivite = ($valeurAjoutee / $masseSalarialeTotal) * 100;
            }
        }

        // ── Niveau d'alerte ───────────────────────────────────────────────────
        $alerte = $this->determinerAlerte(
            ca: $ca,
            seuilRentabilite: $seuilRentabilite,
            tresorerieNette: $tresorerieNette,
            joursTresorerie: $joursTresorerie,
            tauxMargeBrute: $tauxMargeBrute,
            ebe: $ebe,
        );

        return [
            'annee'                  => $fd->annee,
            'mois'                   => $fd->mois,
            'seuil_rentabilite'      => $seuilRentabilite,
            'taux_marge_brute'       => $tauxMargeBrute,
            'marge_brute'            => $margeBrute,
            'ebe'                    => $ebe,
            'taux_ebe'               => $tauxEbe,
            'caf'                    => $caf,
            'tresorerie_nette'       => $tresorerieNette,
            'jours_tresorerie'       => $joursTresorerie,
            'ca_par_salarie'         => $caParSalarie,
            'productivite_salariale' => $productivite,
            'alerte'                 => $alerte,
            'client_id'              => $fd->client_id,
        ];
    }

    /**
     * Détermine le niveau d'alerte (vert / orange / rouge)
     * selon plusieurs critères cumulatifs
     */
    private function determinerAlerte(
        float $ca,
        ?float $seuilRentabilite,
        ?float $tresorerieNette,
        ?int $joursTresorerie,
        ?float $tauxMargeBrute,
        ?float $ebe = null,
    ): string {
        $score = 0; // 0 = vert, 1-2 = orange, 3+ = rouge

        // Le CA ne couvre pas le seuil de rentabilité → ROUGE immédiat
        if ($seuilRentabilite !== null && $ca > 0 && $ca < $seuilRentabilite) {
            $score += 3;
        }

        // Trésorerie négative → ROUGE
        if ($tresorerieNette !== null && $tresorerieNette < 0) {
            $score += 3;
        }

        // EBE négatif → ORANGE (l'exploitation ne dégage pas de valeur)
        if ($ebe !== null && $ebe < 0) {
            $score += 2;
        }

        // Moins de 15 jours de trésorerie → ORANGE
        if ($joursTresorerie !== null && $joursTresorerie < 15 && $joursTresorerie >= 0) {
            $score += 2;
        }

        // Taux de marge brute < 20% → ORANGE (pour secteur services)
        if ($tauxMargeBrute !== null && $tauxMargeBrute < 20 && $tauxMargeBrute >= 0) {
            $score += 1;
        }

        // EBE négatif (calculé via score mais non passé en param — géré via seuil)

        if ($score >= 3) return 'rouge';
        if ($score >= 1) return 'orange';
        return 'vert';
    }

    /**
     * Calcule le seuil de rentabilité mensuel d'un client
     * à partir des données annuelles (utile pour le dashboard)
     */
    public function seuilMensuel(FinancialData $fd): ?float
    {
        $data = $this->calculate($fd);
        $seuil = $data['seuil_rentabilite'];
        return $seuil ? round($seuil / 12, 2) : null;
    }

    /**
     * Génère un commentaire automatique (sans IA) sur les KPIs
     * Sprint 1 : version textuelle simple, sera remplacée par Claude en Sprint 2
     */
    public function genererCommentaire(array $kpiData, FinancialData $fd): string
    {
        $ca    = (float)($fd->ca ?? 0);
        $seuil = $kpiData['seuil_rentabilite'] ?? null;
        $ebe   = $kpiData['ebe'] ?? null;
        $treso = $kpiData['tresorerie_nette'] ?? null;

        $lignes = [];

        // CA vs Seuil
        if ($seuil && $ca > 0) {
            $ecart = $ca - $seuil;
            if ($ecart > 0) {
                $lignes[] = sprintf(
                    '✅ Le CA (%.0f €) dépasse le seuil de rentabilité (%.0f €) de %.0f €.',
                    $ca, $seuil, $ecart
                );
            } else {
                $lignes[] = sprintf(
                    '🚨 Le CA (%.0f €) est inférieur au seuil de rentabilité (%.0f €) de %.0f €. Action urgente requise.',
                    $ca, $seuil, abs($ecart)
                );
            }
        }

        // EBE
        if ($ebe !== null) {
            if ($ebe > 0) {
                $lignes[] = sprintf('✅ EBE positif : %.0f € — l\'entreprise dégage de la valeur.', $ebe);
            } else {
                $lignes[] = sprintf('⚠️ EBE négatif : %.0f € — les charges opérationnelles absorbent tout le CA.', $ebe);
            }
        }

        // Trésorerie
        if ($treso !== null) {
            if ($treso < 0) {
                $lignes[] = '🚨 Trésorerie négative — risque de cessation de paiement à surveiller immédiatement.';
            } elseif ($treso < 5000) {
                $lignes[] = sprintf('⚠️ Trésorerie faible : %.0f € — moins de 15 jours d\'autonomie.', $treso);
            } else {
                $lignes[] = sprintf('✅ Trésorerie : %.0f €.', $treso);
            }
        }

        return implode("\n", $lignes);
    }
}
