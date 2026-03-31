<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comptes Rendus de Réunion
 * Générés via IA (Claude) selon la structure type définie dans la méthodologie
 * Structure : situation actuelle / indicateurs / problèmes / décisions / actions / échéances / impact
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mission_id')->constrained()->onDelete('cascade');

            // En-tête
            $table->string('titre');
            $table->date('date_reunion');
            $table->string('lieu')->nullable();
            $table->text('participants')->nullable();

            // Corps du compte rendu (structure type méthodologie)
            $table->text('situation_actuelle')->nullable();
            $table->json('indicateurs_cles')->nullable();     // JSON des KPIs du moment
            $table->text('problemes_identifies')->nullable();
            $table->text('decisions_prises')->nullable();
            $table->text('actions_a_realiser')->nullable();   // Généré par IA
            $table->text('responsables')->nullable();
            $table->text('echeances')->nullable();
            $table->text('impact_attendu')->nullable();

            // Données brutes pour la génération IA
            $table->text('notes_brutes')->nullable()->comment('Notes prises pendant la réunion, avant traitement IA');
            $table->boolean('genere_par_ia')->default(false);

            // Export
            $table->string('fichier_pdf')->nullable()->comment('Chemin vers le PDF généré');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_reports');
    }
};
