<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\FinancialDataController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\CsvImportController;
use Illuminate\Support\Facades\Route;

// ─── Page d'accueil → redirection vers dashboard ──────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ─── Modèle CSV téléchargeable (pas besoin d'auth) ───────────────────────────
Route::get('/csv/template', [CsvImportController::class, 'template'])->name('csv.template');

// ─── Routes authentifiées ─────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Clients ───────────────────────────────────────────────────────────────
    Route::resource('clients', ClientController::class);

    // ── Missions ──────────────────────────────────────────────────────────────
    Route::resource('missions', MissionController::class);

    // Actions du plan de mission
    Route::post('missions/{mission}/actions', [MissionController::class, 'storeAction'])
        ->name('missions.actions.store');

    Route::patch('missions/{mission}/actions/{action}/statut', [MissionController::class, 'updateActionStatut'])
        ->name('missions.actions.update-statut');

    // ── Données financières ───────────────────────────────────────────────────
    Route::get('/clients/{client}/financial/create', [FinancialDataController::class, 'create'])
        ->name('financial.create');

    Route::post('/clients/{client}/financial', [FinancialDataController::class, 'store'])
        ->name('financial.store');

    Route::get('/financial/{financialData}', [FinancialDataController::class, 'show'])
        ->name('financial-data.show');

    Route::get('/financial/{financialData}/edit', [FinancialDataController::class, 'edit'])
        ->name('financial-data.edit');

    Route::put('/financial/{financialData}', [FinancialDataController::class, 'update'])
        ->name('financial-data.update');

    // ── Import CSV données financières ─────────────────────────────────────────
    Route::get('/clients/{client}/import', [CsvImportController::class, 'form'])
        ->name('clients.csv.form');

    Route::post('/clients/{client}/import', [CsvImportController::class, 'import'])
        ->name('clients.csv.import');

    // ── Analyse IA (Sprint 2) ─────────────────────────────────────────────────
    Route::get('/clients/{client}/analysis', [AnalysisController::class, 'show'])
        ->name('clients.analysis.show');

    Route::post('/clients/{client}/analysis', [AnalysisController::class, 'generate'])
        ->name('clients.analysis.generate');

    Route::post('/clients/{client}/analysis/kpi/{kpi}', [AnalysisController::class, 'forKpi'])
        ->name('clients.analysis.for-kpi');

    // ── Export PDF (Sprint 2) ─────────────────────────────────────────────────
    Route::get('/clients/{client}/pdf', [PdfController::class, 'download'])
        ->name('clients.pdf.download');

    Route::get('/clients/{client}/pdf/kpi/{kpi}', [PdfController::class, 'downloadForKpi'])
        ->name('clients.pdf.for-kpi');

});

// ─── Auth (fourni par Laravel Breeze) ────────────────────────────────────────
require __DIR__ . '/auth.php';
