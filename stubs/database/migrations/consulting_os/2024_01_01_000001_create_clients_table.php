<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 1 – Gestion Clients
 * Fiche client : type d'activité, secteur, données juridiques, statut mission
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Référence au consultant (user Laravel)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Informations générales
            $table->string('raison_sociale');
            $table->string('nom_contact')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();

            // Données juridiques
            $table->string('siret', 14)->nullable();
            $table->string('forme_juridique')->nullable(); // SARL, SAS, EI, etc.
            $table->year('annee_creation')->nullable();

            // Type et secteur d'activité
            $table->enum('type_activite', ['service', 'negoce', 'production', 'mixte'])->default('service');
            $table->string('secteur'); // ex: "Bien-être & Beauté", "Restauration"...

            // Statut de la relation
            $table->enum('statut', ['prospect', 'actif', 'en_pause', 'termine'])->default('prospect');

            // Notes libres
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
