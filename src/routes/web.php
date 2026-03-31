<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialDataController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Page d'accueil → redirection vers dashboard ──────────────────────────────
Route::get('/', fn () => redirect()->route('dashboard'));

// ─── Routes authentifiées (métier) ────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('clients', ClientController::class);

    Route::resource('missions', MissionController::class);
    Route::post('missions/{mission}/actions', [MissionController::class, 'storeAction'])
        ->name('missions.actions.store');
    Route::patch('missions/{mission}/actions/{action}/statut', [MissionController::class, 'updateActionStatut'])
        ->name('missions.actions.update-statut');

    Route::get('/financial/create', [FinancialDataController::class, 'create'])
        ->name('financial-data.create');
    Route::post('/financial', [FinancialDataController::class, 'store'])
        ->name('financial-data.store');
    Route::get('/financial/{financialData}', [FinancialDataController::class, 'show'])
        ->name('financial-data.show');
    Route::get('/financial/{financialData}/edit', [FinancialDataController::class, 'edit'])
        ->name('financial-data.edit');
    Route::put('/financial/{financialData}', [FinancialDataController::class, 'update'])
        ->name('financial-data.update');
    Route::post('/clients/{client}/financial/import-csv', [FinancialDataController::class, 'importCsv'])
        ->name('financial-data.import-csv');
});

// ─── Profil (Breeze : auth seulement) ─────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
