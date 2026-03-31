<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Kpi;
use App\Models\FinancialData;
use App\Services\ClaudeAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gère les analyses IA contextuelles (Sprint 2)
 *
 * Routes :
 *   GET  /clients/{client}/analysis           → show()   Affiche l'analyse existante ou invite à en lancer une
 *   POST /clients/{client}/analysis           → generate() Lance une nouvelle analyse via Claude
 *   GET  /clients/{client}/analysis/kpi/{kpi} → forKpi()  Analyse d'une période spécifique
 */
class AnalysisController extends Controller
{
    public function __construct(private ClaudeAnalysisService $claudeService) {}

    /**
     * Affiche la dernière analyse IA disponible pour ce client
     */
    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $kpi = $client->kpis()->whereNotNull('analyse_ia')->latest()->first();
        $latestKpi = $client->latestKpi();
        $latestFd  = $client->latestFinancialData();

        return view('analysis.show', compact('client', 'kpi', 'latestKpi', 'latestFd'));
    }

    /**
     * Lance une nouvelle analyse Claude et sauvegarde le résultat
     */
    public function generate(Request $request, Client $client)
    {
        $this->authorize('view', $client);

        $kpi = $client->latestKpi();
        $fd  = $client->latestFinancialData();

        if (! $kpi || ! $fd) {
            return back()->with('error', 'Veuillez d\'abord saisir des données financières pour ce client.');
        }

        $analyse = $this->claudeService->analyserClient($client, $kpi, $fd);

        // Persister l'analyse dans le champ analyse_ia du KPI
        $kpi->update([
            'analyse_ia' => json_encode($analyse, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()
            ->route('clients.analysis.show', $client)
            ->with('success', 'Analyse IA générée avec succès.');
    }

    /**
     * Analyse d'un KPI spécifique (période précise)
     */
    public function forKpi(Client $client, Kpi $kpi)
    {
        $this->authorize('view', $client);

        // Vérifier que ce KPI appartient bien à ce client
        abort_if($kpi->client_id !== $client->id, 403);

        $fd = $kpi->financialData ?? $client->latestFinancialData();

        if (! $fd) {
            return back()->with('error', 'Données financières introuvables pour cette période.');
        }

        $analyse = $this->claudeService->analyserClient($client, $kpi, $fd);

        $kpi->update([
            'analyse_ia' => json_encode($analyse, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()
            ->route('clients.analysis.show', $client)
            ->with('success', 'Analyse IA générée pour la période ' . $kpi->periodeLabel() . '.');
    }
}
