<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Kpi;
use App\Models\FinancialData;
use App\Models\Mission;
use App\Services\ClaudeAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Génération et téléchargement du compte rendu PDF (Sprint 2)
 *
 * Utilise la bibliothèque DomPDF (barryvdh/laravel-dompdf)
 * installée via setup.bat (Étape 7).
 *
 * Routes :
 *   GET /clients/{client}/pdf              → dernier KPI
 *   GET /clients/{client}/pdf/kpi/{kpi}   → KPI spécifique
 */
class PdfController extends Controller
{
    public function __construct(private ClaudeAnalysisService $claudeService) {}

    /**
     * Génère le PDF du compte rendu pour le dernier KPI disponible
     */
    public function download(Client $client)
    {
        $this->authorize('view', $client);

        $kpi = $client->latestKpi();
        $fd  = $client->latestFinancialData();

        if (! $kpi || ! $fd) {
            return back()->with('error', 'Aucune donnée financière disponible pour générer le PDF.');
        }

        return $this->genererPdf($client, $kpi, $fd);
    }

    /**
     * Génère le PDF pour un KPI spécifique
     */
    public function downloadForKpi(Client $client, Kpi $kpi)
    {
        $this->authorize('view', $client);
        abort_if($kpi->client_id !== $client->id, 403);

        $fd = $kpi->financialData ?? $client->latestFinancialData();

        if (! $fd) {
            return back()->with('error', 'Données financières introuvables pour cette période.');
        }

        return $this->genererPdf($client, $kpi, $fd);
    }

    // ─── Génération du PDF ────────────────────────────────────────────────────

    private function genererPdf(Client $client, Kpi $kpi, FinancialData $fd)
    {
        // Récupérer ou générer l'analyse IA
        $analyse = null;
        if ($kpi->analyse_ia) {
            $analyse = json_decode($kpi->analyse_ia, true);
        } else {
            $analyse = $this->claudeService->analyserClient($client, $kpi, $fd);
            $kpi->update(['analyse_ia' => json_encode($analyse, JSON_UNESCAPED_UNICODE)]);
        }

        $mission  = $client->missionActive();
        $actions  = $mission ? $mission->actionPlans()->orderBy('priorite')->get() : collect();
        $consultant = Auth::user();
        $periode  = $kpi->periodeLabel();

        // Rendre la vue HTML du rapport
        $html = view('pdf.rapport', compact(
            'client', 'kpi', 'fd', 'analyse', 'mission', 'actions', 'consultant', 'periode'
        ))->render();

        // Générer le PDF via DomPDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
        ]);

        $nomFichier = sprintf(
            'rapport_%s_%s_%s.pdf',
            str()->slug($client->raison_sociale),
            str_replace(' ', '_', strtolower($periode)),
            now()->format('Ymd')
        );

        return $pdf->download($nomFichier);
    }
}
