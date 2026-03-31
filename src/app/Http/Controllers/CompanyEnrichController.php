<?php

namespace App\Http\Controllers;

use App\Services\CompanyData\SireneClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CompanyEnrichController extends Controller
{
    public function __invoke(Request $request, SireneClient $sirene): JsonResponse
    {
        $validated = $request->validate([
            'siret' => 'required|string|min:14|max:20',
        ]);

        if (! config('services.insee.token')) {
            return response()->json([
                'error' => 'missing_insee_token',
                'message' => "Le token INSEE (API Sirene) n'est pas configuré.",
            ], 501);
        }

        $siret = substr(preg_replace('/\D+/', '', $validated['siret']), 0, 14);

        $data = Cache::remember("sirene:siret:{$siret}", now()->addHours(24), function () use ($sirene, $siret) {
            return $sirene->getEtablissementBySiret($siret);
        });

        $etablissement = $data['etablissement'] ?? [];
        $uniteLegale = $etablissement['uniteLegale'] ?? [];
        $adresseEtab = $etablissement['adresseEtablissement'] ?? [];

        $anneeCreation = null;
        $dateCreation = $uniteLegale['dateCreationUniteLegale'] ?? ($etablissement['dateCreationEtablissement'] ?? null);
        if (is_string($dateCreation) && strlen($dateCreation) >= 4) {
            $anneeCreation = (int) substr($dateCreation, 0, 4);
        }

        $naf = $uniteLegale['activitePrincipaleUniteLegale'] ?? ($etablissement['activitePrincipaleEtablissement'] ?? null);
        $categorieJuridique = $uniteLegale['categorieJuridiqueUniteLegale'] ?? null;

        $adresse = trim(implode(' ', array_filter([
            $adresseEtab['numeroVoieEtablissement'] ?? null,
            $adresseEtab['typeVoieEtablissement'] ?? null,
            $adresseEtab['libelleVoieEtablissement'] ?? null,
            $adresseEtab['codePostalEtablissement'] ?? null,
            $adresseEtab['libelleCommuneEtablissement'] ?? null,
        ])));

        $denomination =
            $uniteLegale['denominationUniteLegale'] ??
            $uniteLegale['nomUniteLegale'] ??
            $uniteLegale['nomUsageUniteLegale'] ??
            null;

        $etatAdministratif =
            $uniteLegale['etatAdministratifUniteLegale'] ??
            $etablissement['etatAdministratifEtablissement'] ??
            null;

        $isSiege = (bool) ($etablissement['etablissementSiege'] ?? false);

        $effectifTranche =
            $etablissement['trancheEffectifsEtablissement'] ??
            $uniteLegale['trancheEffectifsUniteLegale'] ??
            null;

        $effectifAnnee =
            $etablissement['anneeEffectifsEtablissement'] ??
            $uniteLegale['anneeEffectifsUniteLegale'] ??
            null;

        return response()->json([
            'siret' => $etablissement['siret'] ?? $siret,
            'siren' => $etablissement['siren'] ?? substr($siret, 0, 9),
            'raison_sociale' => $denomination,
            'adresse' => $adresse ?: null,
            'adresse_details' => [
                'numero_voie' => $adresseEtab['numeroVoieEtablissement'] ?? null,
                'type_voie' => $adresseEtab['typeVoieEtablissement'] ?? null,
                'libelle_voie' => $adresseEtab['libelleVoieEtablissement'] ?? null,
                'complement_adresse' => $adresseEtab['complementAdresseEtablissement'] ?? null,
                'code_postal' => $adresseEtab['codePostalEtablissement'] ?? null,
                'commune' => $adresseEtab['libelleCommuneEtablissement'] ?? null,
                'code_commune_insee' => $adresseEtab['codeCommuneEtablissement'] ?? null,
            ],
            'naf' => $naf,
            'categorie_juridique' => $categorieJuridique,
            'annee_creation' => $anneeCreation,
            'etat_administratif' => $etatAdministratif,
            'siege' => $isSiege,
            'effectif' => [
                'tranche' => $effectifTranche,
                'annee' => $effectifAnnee,
            ],
            'raw' => $data,
        ]);
    }
}
