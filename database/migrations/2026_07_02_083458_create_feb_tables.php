<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Agences
        Schema::create('feb_agences', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 20)->nullable();
            $table->string('localite')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Colonnes disponibles (créées par l'admin)
        Schema::create('feb_colonnes', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('description')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Utilisateurs FEB (distinct des users Laravel)
        Schema::create('feb_utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('identifiant')->unique();
            $table->string('password');
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('poste')->nullable();
            $table->unsignedBigInteger('agence_id')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->foreign('agence_id')->references('id')->on('feb_agences')->onDelete('set null');
        });

        // Fiches d'expression des besoins
        Schema::create('feb_fiches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('statut', 20)->default('brouillon'); // brouillon | soumise
            $table->boolean('vue_admin')->default(false);
            $table->unsignedBigInteger('modele_id')->nullable(); // fiche source si créée à partir d'un modèle
            $table->timestamp('soumise_at')->nullable();
            $table->timestamps();

            $table->foreign('utilisateur_id')->references('id')->on('feb_utilisateurs')->onDelete('cascade');
        });

        // Sections d'une fiche
        Schema::create('feb_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fiche_id');
            $table->string('titre');
            $table->integer('ordre')->default(0);
            $table->timestamps();

            $table->foreign('fiche_id')->references('id')->on('feb_fiches')->onDelete('cascade');
        });

        // Colonnes cochées dans une section
        Schema::create('feb_section_colonnes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('colonne_id');
            $table->integer('ordre')->default(0);

            $table->foreign('section_id')->references('id')->on('feb_sections')->onDelete('cascade');
            $table->foreign('colonne_id')->references('id')->on('feb_colonnes')->onDelete('cascade');
        });

        // Lignes du tableau dans une section
        Schema::create('feb_lignes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->integer('numero_ligne');
            $table->json('valeurs'); // { colonne_id: valeur, ... }
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('feb_sections')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feb_lignes');
        Schema::dropIfExists('feb_section_colonnes');
        Schema::dropIfExists('feb_sections');
        Schema::dropIfExists('feb_fiches');
        Schema::dropIfExists('feb_utilisateurs');
        Schema::dropIfExists('feb_colonnes');
        Schema::dropIfExists('feb_agences');
    }
};