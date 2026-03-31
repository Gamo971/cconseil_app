<?php

namespace App\Http\Controllers;

use App\Services\CompanyData\RechercheEntreprisesClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanySearchController extends Controller
{
    public function __invoke(Request $request, RechercheEntreprisesClient $client): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:120',
            'page' => 'sometimes|integer|min:1|max:50',
            'per_page' => 'sometimes|integer|min:1|max:25',
        ]);

        $payload = $client->search(
            q: $validated['q'],
            page: $validated['page'] ?? 1,
            perPage: $validated['per_page'] ?? 10,
        );

        $results = array_map(function (array $r): array {
            $siege = is_array($r['siege'] ?? null) ? $r['siege'] : [];

            $raisonSociale =
                $r['nom_complet'] ??
                $r['denomination'] ??
                $r['raison_sociale'] ??
                $r['nom_raison_sociale'] ??
                null;

            $adresse =
                $siege['adresse'] ??
                trim(implode(' ', array_filter([
                    $siege['adresse_ligne_1'] ?? null,
                    $siege['adresse_ligne_2'] ?? null,
                    $siege['code_postal'] ?? null,
                    $siege['libelle_commune'] ?? null,
                ])));

            $siret = $siege['siret'] ?? ($r['siret'] ?? null);
            $siren = $r['siren'] ?? (is_string($siret) ? substr(preg_replace('/\D+/', '', $siret), 0, 9) : null);

            return [
                'siren' => $siren,
                'siret' => $siret,
                'raison_sociale' => $raisonSociale,
                'adresse' => $adresse ?: null,
                'naf' => $r['activite_principale'] ?? ($siege['activite_principale'] ?? null),
                'categorie_juridique' => $r['categorie_juridique'] ?? null,
                'forme_juridique' => $r['forme_juridique'] ?? null,
                'annee_creation' => $r['annee_creation'] ?? null,
                'etat_administratif' => $r['etat_administratif'] ?? null,
                'label' => trim(implode(' — ', array_filter([
                    $raisonSociale,
                    $siret ?: $siren,
                    $adresse,
                ]))),
                'raw' => $r,
            ];
        }, $payload['results'] ?? []);

        return response()->json([
            'meta' => [
                'total' => $payload['total_results'] ?? null,
                'page' => $payload['page'] ?? null,
                'per_page' => $payload['per_page'] ?? null,
            ],
            'results' => $results,
        ]);
    }
}
