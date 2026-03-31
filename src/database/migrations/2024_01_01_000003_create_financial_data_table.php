<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 2 – Collecte Données Financières
 * Saisie mensuelle : CA, charges, masse salariale, dettes, investissements
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_data', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            // Période concernée
            $table->year('annee');
            $table->tinyInteger('mois')->nullable(); // null = données annuelles

            // ── Compte de résultat ──────────────────────────────────────────
            $table->decimal('ca', 12, 2)->nullable()->comment("Chiffre d'affaires HT");
            $table->decimal('achats_marchandises', 12, 2)->nullable();
            $table->decimal('autres_achats', 12, 2)->nullable();

            // ── Charges ────────────────────────────────────────────────────
            $table->decimal('charges_fixes', 12, 2)->nullable()->comment('Loyer, assurances, abonnements...');
            $table->decimal('charges_variables', 12, 2)->nullable()->comment('Matières, fournitures...');

            // ── Masse salariale ────────────────────────────────────────────
            $table->decimal('masse_salariale_brute', 12, 2)->nullable();
            $table->decimal('charges_patronales', 12, 2)->nullable();
            $table->integer('nombre_salaries')->nullable();

            // ── Dettes & Investissements ───────────────────────────────────
            $table->decimal('dette_totale', 12, 2)->nullable();
            $table->decimal('dette_fournisseurs', 12, 2)->nullable();
            $table->decimal('dette_fiscale_sociale', 12, 2)->nullable();
            $table->decimal('investissements', 12, 2)->nullable();
            $table->decimal('amortissements', 12, 2)->nullable();

            // ── Trésorerie ─────────────────────────────────────────────────
            $table->decimal('tresorerie_debut', 12, 2)->nullable();
            $table->decimal('tresorerie_fin', 12, 2)->nullable();

            // Source de la donnée
            $table->enum('source', ['saisie_manuelle', 'import_csv', 'import_api'])->default('saisie_manuelle');
            $table->text('notes')->nullable();

            $table->timestamps();

            // Un seul enregistrement par client / année / mois
            $table->unique(['client_id', 'annee', 'mois']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_data');
    }
};
