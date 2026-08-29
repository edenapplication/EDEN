<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. rh_types_contrat
        Schema::create('rh_types_contrat', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 100);
            $table->string('categorie', 50)->nullable();
            $table->boolean('est_renouvelable')->default(false);
            $table->integer('duree_maximale_mois')->nullable();
            $table->integer('periode_essai_max_jours')->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // 2. rh_agences_sites
        Schema::create('rh_agences_sites', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 100);
            $table->string('ville', 100)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // 3. rh_niveaux_chelons
        Schema::create('rh_niveaux_chelons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('nom', 50);
            $table->string('categorie', 20)->nullable();
            $table->integer('ordre')->default(0);
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // 4. rh_sources_candidature
        Schema::create('rh_sources_candidature', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 100);
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // 5. rh_types_absence
        Schema::create('rh_types_absence', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 100);
            $table->boolean('est_remunere')->default(true);
            $table->boolean('necessite_justificatif')->default(false);
            $table->integer('plafond_jours_annuel')->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // 6. rh_types_sanction
        Schema::create('rh_types_sanction', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 100);
            $table->enum('gravite', ['faible', 'moyenne', 'elevee', 'tres_elevee'])->default('moyenne');
            $table->boolean('impact_financier')->default(false);
            $table->boolean('impact_carriere')->default(false);
            $table->integer('duree_max_jours')->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // 7. rh_types_formation
        Schema::create('rh_types_formation', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 100);
            $table->string('categorie', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // 8. rh_motifs_depart
        Schema::create('rh_motifs_depart', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom', 100);
            $table->string('categorie', 50)->nullable();
            $table->boolean('est_volontaire')->default(true);
            $table->boolean('necessite_preavis')->default(true);
            $table->integer('preavis_jours')->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // 9. rh_horaires_services
        Schema::create('rh_horaires_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('rh_services')->onDelete('cascade');
            $table->enum('jour', ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche']);
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->boolean('est_ferie')->default(false);
            $table->boolean('est_travaille')->default(true);
            $table->text('remarque')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'jour']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_horaires_services');
        Schema::dropIfExists('rh_motifs_depart');
        Schema::dropIfExists('rh_types_formation');
        Schema::dropIfExists('rh_types_sanction');
        Schema::dropIfExists('rh_types_absence');
        Schema::dropIfExists('rh_sources_candidature');
        Schema::dropIfExists('rh_niveaux_chelons');
        Schema::dropIfExists('rh_agences_sites');
        Schema::dropIfExists('rh_types_contrat');
    }
};