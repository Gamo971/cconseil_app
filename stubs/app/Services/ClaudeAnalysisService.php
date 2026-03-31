<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Kpi;
use App\Models\FinancialData;
use App\Models\Mission;
use Anthropic\Laravel\Facades\Anthropic;

/**
 * Service d'analyse contextuelle via l'API Claude (Anthropic)
 *
 * Génère :
 *   - Un diagnostic financier personnalisé (secteur + KPIs)
 *   - Un plan d'action recommandé (3 actions prioritaires)
 *   - Un résumé de synthèse pour le compte rendu PDF
 */
class ClaudeAnalysisService
{
    /**
     * Lance une analyse complète pour un client donné
     * Retourne un tableau structuré : diagnostic, recommandations, synthese
     */
    public function analyserClient(Client $client, Kpi $kpi, FinancialData $fd): array
    {
        $prompt = $this->construirePrompt($client, $kpi, $fd);

        try {
            $response = Anthropic::messages()->create([
                'model'      => 'claude-opus-4-6',
                'max_tokens' => 1500,
                'system'     => $this->systemPrompt(),
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $texte = $response->content[0]->text ?? '';

            return $this->parseReponse($texte);

        } catch (\Exception $e) {
            // En cas d'erreur API, retourner une analyse de secours basée sur les règles
            return $this->analyseSecours($kpi, $fd);
        }
    }

    /**
     * Analyse rapide (résumé court) pour la vue récapitulative
     */
    public function synthese(Client $client, Kpi $kpi, FinancialData $fd): string
    {
        $analyse = $this->analyserClient($client, $kpi, $fd);
        return $analyse['synthese'] ?? '';
    }

    // ─── Construction du prompt ────────────────────────────────────────────────

    private function systemPrompt(): string
    {
        return <<<SYSTEM
Tu es un consultant financier senior spécialisé en PME/TPE françaises, expert en analyse de performance et en plans de redressement.
Tu analyses les indicateurs financiers fournis et tu produis des recommandations concrètes, actionnables et adaptées au contexte sectoriel.
Tu réponds toujours en français, avec un ton professionnel mais accessible.
Tu structures toujours ta réponse avec exactement trois sections séparées par des balises XML :
<diagnostic>...</diagnostic>
<recommandations>...</recommandations>
<synthese>...</synthese>
SYSTEM;
    }

    private function construirePrompt(Client $client, Kpi $kpi, FinancialData $fd): string
    {
        $alerte     = strtoupper($kpi->alerte ?? 'NC');
        $periode    = $kpi->periodeLabel();
        $secteur    = $client->secteur ?? 'non renseigné';
        $type       = $client->typeActiviteLabel();
        $mission    = $client->missionActive();
        $objectif   = $mission?->objectif_global ?? 'Non renseigné';
        $phase      = $mission?->phaseLabel() ?? 'Aucune mission active';

        $ca          = Kpi::formatEur((float)$fd->ca);
        $seuil       = Kpi::formatEur($kpi->seuil_rentabilite);
        $marge       = Kpi::formatPct($kpi->taux_marge_brute);
        $ebe         = Kpi::formatEur($kpi->ebe);
        $tauxEbe     = Kpi::formatPct($kpi->taux_ebe);
        $treso       = Kpi::formatEur($kpi->tresorerie_nette);
        $jours       = $kpi->jours_tresorerie !== null ? $kpi->jours_tresorerie . ' jours' : '—';
        $caf         = Kpi::formatEur($kpi->caf);
        $caSal       = Kpi::formatEur($kpi->ca_par_salarie);
        $nbSal       = $fd->nombre_salaries ?? '—';

        return <<<PROMPT
Voici les données financières d'un client en consultation.

## Client
- Secteur : {$secteur}
- Type d'activité : {$type}
- Période analysée : {$periode}
- Niveau d'alerte global : {$alerte}

## Objectif de mission
{$objectif}

## Phase de la mission
{$phase}

## Indicateurs financiers
| Indicateur              | Valeur         |
|-------------------------|----------------|
| Chiffre d'affaires      | {$ca}          |
| Seuil de rentabilité    | {$seuil}       |
| Taux de marge brute     | {$marge}       |
| EBE                     | {$ebe}         |
| Taux EBE                | {$tauxEbe}     |
| CAF                     | {$caf}         |
| Trésorerie nette        | {$treso}       |
| Jours de trésorerie     | {$jours}       |
| CA / salarié            | {$caSal}       |
| Nombre de salariés      | {$nbSal}       |

## Instructions
1. Dans <diagnostic> : analyse en 3-4 phrases les points forts et les risques majeurs identifiés, en tenant compte du secteur.
2. Dans <recommandations> : liste exactement 3 actions prioritaires numérotées, concrètes et actionnables à court terme (< 90 jours).
3. Dans <synthese> : rédige en 2 phrases maximum un résumé exécutif pour un compte rendu client.
PROMPT;
    }

    // ─── Parsing de la réponse ─────────────────────────────────────────────────

    private function parseReponse(string $texte): array
    {
        $diagnostic      = $this->extraireSection($texte, 'diagnostic');
        $recommandations = $this->extraireSection($texte, 'recommandations');
        $synthese        = $this->extraireSection($texte, 'synthese');

        return [
            'diagnostic'      => $diagnostic,
            'recommandations' => $recommandations,
            'synthese'        => $synthese,
            'texte_complet'   => $texte,
        ];
    }

    private function extraireSection(string $texte, string $balise): string
    {
        preg_match("/<{$balise}>(.*?)<\/{$balise}>/s", $texte, $matches);
        return trim($matches[1] ?? '');
    }

    // ─── Analyse de secours (sans API) ────────────────────────────────────────

    /**
     * Si l'API Claude est indisponible ou la clé non configurée,
     * génère une analyse textuelle basée sur les règles KPI du Sprint 1.
     */
    private function analyseSecours(Kpi $kpi, FinancialData $fd): array
    {
        $ca    = (float)($fd->ca ?? 0);
        $seuil = (float)($kpi->seuil_rentabilite ?? 0);
        $ebe   = (float)($kpi->ebe ?? 0);
        $treso = (float)($kpi->tresorerie_nette ?? 0);
        $marge = (float)($kpi->taux_marge_brute ?? 0);
        $jours = (int)($kpi->jours_tresorerie ?? 999);

        $points  = [];
        $actions = [];

        // Diagnostic
        if ($seuil > 0 && $ca < $seuil) {
            $ecart    = number_format($seuil - $ca, 0, ',', ' ');
            $points[] = "Le chiffre d'affaires est inférieur au seuil de rentabilité de {$ecart} €, ce qui génère une situation structurellement déficitaire.";
            $actions[] = "1. Identifier les leviers d'augmentation du CA (nouvelles offres, upsell, prospection) avec un objectif de +{$ecart} € sur 90 jours.";
        } else {
            $ecart    = number_format($ca - $seuil, 0, ',', ' ');
            $points[] = "Le CA dépasse le seuil de rentabilité de {$ecart} €, ce qui constitue un point de stabilité.";
        }

        if ($ebe < 0) {
            $points[]  = "L'EBE négatif indique que les charges opérationnelles absorbent l'intégralité de la valeur créée.";
            $actions[] = "2. Auditer les charges variables et la masse salariale pour identifier les postes de réduction rapide.";
        } else {
            $points[] = "L'EBE positif confirme que l'activité dégage de la valeur opérationnelle.";
        }

        if ($treso < 0) {
            $points[]  = "La trésorerie négative expose l'entreprise à un risque immédiat de cessation de paiement.";
            $actions[] = "2. Mettre en place un plan de trésorerie sur 13 semaines et négocier un découvert bancaire ou un affacturage.";
        } elseif ($jours < 15) {
            $points[]  = "La trésorerie couvre moins de 15 jours d'activité, ce qui laisse très peu de marge de manœuvre.";
            $actions[] = "3. Accélérer les encaissements clients et décaler les paiements fournisseurs non critiques.";
        }

        if ($marge < 20) {
            $points[]  = "Le taux de marge brute sous les 20% est faible pour un secteur de services.";
            $actions[] = "3. Revoir la politique tarifaire ou renégocier les achats pour améliorer le taux de marge.";
        }

        // Compléter à 3 actions si nécessaire
        if (count($actions) < 3) {
            $actions[] = count($actions) + 1 . ". Mettre en place un tableau de bord mensuel de suivi des KPIs pour anticiper les dérives.";
        }
        if (count($actions) < 3) {
            $actions[] = count($actions) + 1 . ". Planifier une réunion de revue stratégique trimestrielle avec les parties prenantes.";
        }

        $diagnostic = implode(' ', array_slice($points, 0, 4));
        $recommandations = implode("\n", array_slice($actions, 0, 3));

        $alerte = $kpi->alerte ?? 'vert';
        $synthese = match($alerte) {
            'rouge'  => "La situation financière présente des signaux d'alerte critiques nécessitant une intervention immédiate. Un plan de redressement structuré doit être engagé dans les 30 prochains jours.",
            'orange' => "La situation est stable mais fragile, avec des indicateurs à surveiller de près. Des actions correctives ciblées permettront d'éviter une dégradation dans les prochains mois.",
            default  => "La situation financière est saine et les indicateurs sont dans les normes sectorielles. L'objectif est de consolider les acquis et d'optimiser la performance.",
        };

        return [
            'diagnostic'      => $diagnostic,
            'recommandations' => $recommandations,
            'synthese'        => $synthese,
            'texte_complet'   => "[Analyse générée automatiquement — clé API Claude non configurée]\n\n{$diagnostic}\n\n{$recommandations}",
        ];
    }
}
