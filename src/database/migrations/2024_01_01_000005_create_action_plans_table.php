<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 5 – Plan d'Action
 * Objectifs SMART, suivi avancement, alertes, statuts rouge/orange/vert
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mission_id')->constrained()->onDelete('cascade');

            // L'objectif
            $table->string('objectif');
            $table->text('description')->nullable();

            // KPI cible (ex: "+15% panier moyen")
            $table->string('kpi_cible');
            $table->decimal('valeur_cible', 10, 2)->nullable();
            $table->decimal('valeur_actuelle', 10, 2)->nullable();
            $table->string('unite')->nullable()->comment('€, %, nombre...');

            // Impact estimé
            $table->decimal('impact_estime_eur', 10, 2)->nullable();

            // Responsable
            $table->string('responsable')->nullable();

            // Calendrier
            $table->date('date_limite');
            $table->date('date_realisation')->nullable();

            // Statut visuel
            $table->enum('statut', ['non_commence', 'en_cours', 'termine', 'en_retard'])->default('non_commence');
            $table->enum('alerte', ['vert', 'orange', 'rouge'])->default('vert');

            // Priorité
            $table->tinyInteger('priorite')->default(2)->comment('1=haute, 2=normale, 3=basse');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_plans');
    }
};
