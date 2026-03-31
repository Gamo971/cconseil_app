<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 3 – Moteur d'Indicateurs (KPIs)
 * Calculés automatiquement depuis financial_data + commercial_data
 * Stockés pour historique et comparaison
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('financial_data_id')->nullable()->constrained('financial_data')->nullOnDelete();

            // Période
            $table->year('annee');
            $table->tinyInteger('mois')->nullable();

            // ── Rentabilité ────────────────────────────────────────────────
            $table->decimal('seuil_rentabilite', 12, 2)->nullable()->comment('Charges fixes / Taux de marge');
            $table->decimal('taux_marge_brute', 5, 2)->nullable()->comment('En %');
            $table->decimal('marge_brute', 12, 2)->nullable();

            // ── EBE / EBITDA ───────────────────────────────────────────────
            $table->decimal('ebe', 12, 2)->nullable()->comment('Excédent Brut d\'Exploitation');
            $table->decimal('taux_ebe', 5, 2)->nullable()->comment('EBE / CA en %');

            // ── Capacité d'autofinancement ──────────────────────────────────
            $table->decimal('caf', 12, 2)->nullable()->comment('Capacité d\'Autofinancement');

            // ── Trésorerie ─────────────────────────────────────────────────
            $table->decimal('tresorerie_nette', 12, 2)->nullable();
            $table->integer('jours_tresorerie')->nullable()->comment('Autonomie en jours');

            // ── Productivité ───────────────────────────────────────────────
            $table->decimal('ca_par_salarie', 12, 2)->nullable();
            $table->decimal('productivite_salariale', 5, 2)->nullable()->comment('VA / Masse salariale');

            // ── Commercial (si renseigné) ──────────────────────────────────
            $table->decimal('panier_moyen', 8, 2)->nullable();
            $table->integer('nombre_clients')->nullable();
            $table->decimal('taux_remplissage', 5, 2)->nullable()->comment('En %');
            $table->decimal('ca_par_cabine_jour', 8, 2)->nullable();

            // Statut d'alerte calculé automatiquement
            $table->enum('alerte', ['vert', 'orange', 'rouge'])->default('vert');
            $table->text('analyse_ia')->nullable()->comment('Synthèse générée par Claude/OpenAI');

            $table->timestamps();

            $table->unique(['client_id', 'annee', 'mois']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
