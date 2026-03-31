<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 5 – Suivi de Mission
 * Phases, livrables, dates, statut (rouge/orange/vert)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            // Type et description
            $table->string('type_mission'); // ex: "Restructuration 90 jours", "Diagnostic"
            $table->text('objectif_global')->nullable();

            // Calendrier
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_jours')->virtualAs('DATEDIFF(date_fin, date_debut)');

            // Phase courante (correspondant à la méthodologie 4 phases)
            $table->enum('phase_courante', [
                'phase_1_diagnostic',
                'phase_2_plan_action',
                'phase_3_pilotage',
                'phase_4_optimisation',
                'terminee'
            ])->default('phase_1_diagnostic');

            // Statut visuel pour le dashboard
            $table->enum('statut', ['vert', 'orange', 'rouge', 'termine'])->default('vert');

            // Honoraires
            $table->decimal('honoraires_ht', 10, 2)->nullable();
            $table->enum('mode_facturation', ['forfait', 'mensuel', 'journalier'])->default('forfait');

            // Notes de mission
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
