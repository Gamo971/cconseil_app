<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FinancialData;
use App\Services\KpiCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FinancialDataController extends Controller
{
    public function __construct(
        private KpiCalculatorService $kpiCalculator
    ) {}

    public function create(Request $request): View
    {
        $client = Client::where('id', $request->client_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $anneesCourantes = range(date('Y'), date('Y') - 5);

        return view('financial.create', compact('client', 'anneesCourantes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'annee'                  => 'required|integer|min:2015|max:' . (date('Y') + 1),
            'mois'                   => 'nullable|integer|min:1|max:12',
            'ca'                     => 'nullable|numeric|min:0',
            'achats_marchandises'    => 'nullable|numeric|min:0',
            'autres_achats'          => 'nullable|numeric|min:0',
            'charges_fixes'          => 'nullable|numeric|min:0',
            'charges_variables'      => 'nullable|numeric|min:0',
            'masse_salariale_brute'  => 'nullable|numeric|min:0',
            'charges_patronales'     => 'nullable|numeric|min:0',
            'nombre_salaries'        => 'nullable|integer|min:0',
            'dette_totale'           => 'nullable|numeric|min:0',
            'dette_fournisseurs'     => 'nullable|numeric|min:0',
            'dette_fiscale_sociale'  => 'nullable|numeric|min:0',
            'investissements'        => 'nullable|numeric|min:0',
            'amortissements'         => 'nullable|numeric|min:0',
            'tresorerie_debut'       => 'nullable|numeric',
            'tresorerie_fin'         => 'nullable|numeric',
            'notes'                  => 'nullable|string',
        ]);

        // Vérifier que le client appartient à l'utilisateur
        $client = Client::where('id', $validated['client_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Créer ou mettre à jour la donnée financière
        $fd = FinancialData::updateOrCreate(
            [
                'client_id' => $validated['client_id'],
                'annee'     => $validated['annee'],
                'mois'      => $validated['mois'] ?? null,
            ],
            array_merge($validated, ['source' => 'saisie_manuelle'])
        );

        // Calculer et sauvegarder les KPIs automatiquement
        $kpi = $this->kpiCalculator->calculateAndSave($fd);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', "Données financières enregistrées et indicateurs calculés pour {$fd->periodeLabel()}.");
    }

    public function show(FinancialData $financialData): View
    {
        $this->authorize('view', $financialData);

        $financialData->load(['client', 'kpi']);
        $kpiData = $financialData->kpi
            ? $financialData->kpi->toArray()
            : $this->kpiCalculator->calculate($financialData);

        $commentaire = $this->kpiCalculator->genererCommentaire($kpiData, $financialData);

        return view('financial.show', compact('financialData', 'kpiData', 'commentaire'));
    }

    public function edit(FinancialData $financialData): View
    {
        $this->authorize('update', $financialData);
        $financialData->load('client');
        $anneesCourantes = range(date('Y'), date('Y') - 5);

        return view('financial.edit', compact('financialData', 'anneesCourantes'));
    }

    public function update(Request $request, FinancialData $financialData): RedirectResponse
    {
        $this->authorize('update', $financialData);

        $validated = $request->validate([
            'ca'                     => 'nullable|numeric|min:0',
            'achats_marchandises'    => 'nullable|numeric|min:0',
            'autres_achats'          => 'nullable|numeric|min:0',
            'charges_fixes'          => 'nullable|numeric|min:0',
            'charges_variables'      => 'nullable|numeric|min:0',
            'masse_salariale_brute'  => 'nullable|numeric|min:0',
            'charges_patronales'     => 'nullable|numeric|min:0',
            'nombre_salaries'        => 'nullable|integer|min:0',
            'dette_totale'           => 'nullable|numeric|min:0',
            'dette_fournisseurs'     => 'nullable|numeric|min:0',
            'dette_fiscale_sociale'  => 'nullable|numeric|min:0',
            'investissements'        => 'nullable|numeric|min:0',
            'amortissements'         => 'nullable|numeric|min:0',
            'tresorerie_debut'       => 'nullable|numeric',
            'tresorerie_fin'         => 'nullable|numeric',
            'notes'                  => 'nullable|string',
        ]);

        $financialData->update($validated);

        // Recalculer les KPIs
        $this->kpiCalculator->calculateAndSave($financialData);

        return redirect()
            ->route('clients.show', $financialData->client)
            ->with('success', 'Données financières mises à jour et indicateurs recalculés.');
    }

    /**
     * Import CSV (Sprint 2 — placeholder)
     */
    public function importCsv(Request $request, Client $client): RedirectResponse
    {
        return redirect()->back()->with('info', 'L\'import CSV sera disponible dans la prochaine version.');
    }
}
