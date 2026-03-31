<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FinancialData;
use App\Services\KpiCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Import de données financières via fichier CSV (Sprint 2)
 *
 * Format attendu du CSV :
 *   annee,mois,ca,achats_marchandises,autres_achats,charges_variables,
 *   charges_fixes,masse_salariale_brute,charges_patronales,amortissements,
 *   tresorerie_fin,nombre_salaries
 *
 * Routes :
 *   GET  /clients/{client}/import → form()   Affiche le formulaire d'import
 *   POST /clients/{client}/import → import() Traite le fichier CSV
 */
class CsvImportController extends Controller
{
    public function __construct(private KpiCalculatorService $kpiService) {}

    /**
     * Affiche le formulaire d'import CSV
     */
    public function form(Client $client)
    {
        $this->authorize('update', $client);

        return view('financial.import', compact('client'));
    }

    /**
     * Traite le fichier CSV et importe les données financières
     */
    public function import(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'csv_file.required' => 'Veuillez sélectionner un fichier CSV.',
            'csv_file.mimes'    => 'Le fichier doit être au format CSV (.csv ou .txt).',
            'csv_file.max'      => 'Le fichier ne doit pas dépasser 2 Mo.',
        ]);

        $fichier = $request->file('csv_file');
        $lignes  = array_map('str_getcsv', file($fichier->getRealPath()));

        if (count($lignes) < 2) {
            return back()->with('error', 'Le fichier CSV est vide ou ne contient pas d\'en-tête.');
        }

        $entetes  = array_map('trim', array_shift($lignes));
        $colonnes = $this->colonnesAttendues();

        // Vérification des colonnes obligatoires
        $manquantes = array_diff(['annee', 'ca'], $entetes);
        if (! empty($manquantes)) {
            return back()->with('error', 'Colonnes obligatoires manquantes : ' . implode(', ', $manquantes));
        }

        $importes = 0;
        $erreurs  = [];

        foreach ($lignes as $i => $ligne) {
            $numLigne = $i + 2; // 1-indexed, +1 pour l'en-tête

            if (count($ligne) !== count($entetes)) {
                $erreurs[] = "Ligne {$numLigne} : nombre de colonnes incorrect.";
                continue;
            }

            $row = array_combine($entetes, $ligne);
            $row = array_map('trim', $row);

            // Validation de la ligne
            $validator = Validator::make($row, [
                'annee' => 'required|integer|min:2000|max:2099',
                'ca'    => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                $erreurs[] = "Ligne {$numLigne} : " . $validator->errors()->first();
                continue;
            }

            // Construire les données financières
            $data = ['client_id' => $client->id];
            foreach ($colonnes as $colonne => $type) {
                if (array_key_exists($colonne, $row)) {
                    $val = $row[$colonne];
                    $data[$colonne] = ($val === '' || $val === null)
                        ? null
                        : ($type === 'int' ? (int)$val : (float)$val);
                }
            }

            // Créer ou mettre à jour la ligne
            $fd = FinancialData::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'annee'     => (int)$row['annee'],
                    'mois'      => isset($row['mois']) && $row['mois'] !== '' ? (int)$row['mois'] : null,
                ],
                $data
            );

            // Déclencher le calcul des KPIs
            $this->kpiService->calculateAndSave($fd);

            $importes++;
        }

        $msg = "{$importes} ligne(s) importée(s) avec succès.";
        if (! empty($erreurs)) {
            $msg .= ' ' . count($erreurs) . ' erreur(s) : ' . implode(' | ', array_slice($erreurs, 0, 5));
        }

        $type = empty($erreurs) ? 'success' : 'warning';

        return redirect()
            ->route('clients.show', $client)
            ->with($type, $msg);
    }

    // ─── Modèle CSV à télécharger ─────────────────────────────────────────────

    /**
     * Télécharge un fichier CSV modèle pré-rempli avec les en-têtes
     */
    public function template()
    {
        $entetes = array_keys($this->colonnesAttendues());
        $exemple = [2024, 1, 50000, 8000, 2000, 5000, 15000, 8000, 3000, 500, 12000, 3];

        $contenu  = implode(',', $entetes) . "\n";
        $contenu .= implode(',', $exemple) . "\n";

        return response($contenu, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="modele_import_financier.csv"',
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function colonnesAttendues(): array
    {
        return [
            'annee'                  => 'int',
            'mois'                   => 'int',
            'ca'                     => 'float',
            'achats_marchandises'    => 'float',
            'autres_achats'          => 'float',
            'charges_variables'      => 'float',
            'charges_fixes'          => 'float',
            'masse_salariale_brute'  => 'float',
            'charges_patronales'     => 'float',
            'amortissements'         => 'float',
            'tresorerie_fin'         => 'float',
            'nombre_salaries'        => 'int',
        ];
    }
}
