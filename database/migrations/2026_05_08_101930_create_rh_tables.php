<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // DIRECTIONS
        Schema::create('rh_directions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // SERVICES
        Schema::create('rh_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direction_id')->constrained('rh_directions')->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // POSTES
        Schema::create('rh_postes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direction_id')->nullable()->constrained('rh_directions')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('rh_services')->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('intitule');
            $table->timestamps();
        });

        // EMPLOYÉS
        Schema::create('rh_employes', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 20)->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->enum('sexe', ['M', 'F']);
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('numero_cni', 50)->nullable();
            $table->string('niu', 30)->nullable();
            $table->string('origines')->nullable();
            $table->enum('situation_matrimoniale', ['Marié(e)', 'Célibataire', 'Fiancé(e)', 'Divorcé(e)', 'Veuf(ve)'])->default('Célibataire');
            $table->integer('nb_enfants')->default(0);
            $table->enum('etat_sante', ['BON', 'ASTHMATIQUE', 'DIABETIQUE', 'AUTRE'])->default('BON');
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->string('personne_a_contacter')->nullable();
            $table->string('tel_urgence')->nullable();
            $table->foreignId('direction_id')->nullable()->constrained('rh_directions')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('rh_services')->nullOnDelete();
            $table->foreignId('poste_id')->nullable()->constrained('rh_postes')->nullOnDelete();
            $table->string('intitule_poste')->nullable();
            $table->enum('type_contrat', ['CDI', 'CDD', 'PRE-EMPLOI', 'STAGE', 'PRESTATAIRE'])->default('CDI');
            $table->string('categorie', 10)->nullable();
            $table->date('date_integration');
            $table->date('date_sortie')->nullable();
            $table->string('cause_depart')->nullable();
            $table->enum('vague_paiement', ['VAGUE 1', 'VAGUE 2'])->nullable();
            $table->string('niveau_academique')->nullable();
            $table->string('specialite_academique')->nullable();
            $table->string('diplome_recrutement')->nullable();
            $table->string('exp_poste_precedent')->nullable();
            $table->string('entreprise_precedente')->nullable();
            $table->string('duree_exp_precedente')->nullable();
            $table->decimal('salaire_base', 12, 2)->default(0);
            $table->integer('solde_conges')->default(15);
            $table->integer('conges_pris')->default(0);
            $table->boolean('actif')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // BULLETINS DE PAIE
        Schema::create('rh_bulletins_paie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('rh_employes')->cascadeOnDelete();
            $table->string('periode', 7);
            $table->string('mois_annee')->nullable();
            $table->date('date_paiement');
            $table->enum('vague', ['VAGUE 1', 'VAGUE 2']);
            $table->decimal('salaire_brut', 12, 2)->default(0);
            $table->decimal('salaire_heure', 10, 4)->default(0);
            $table->decimal('prime', 12, 2)->default(0);
            $table->decimal('indemnite', 12, 2)->default(0);
            $table->decimal('nb_heures_sup', 6, 2)->default(0);
            $table->decimal('montant_heures_sup', 12, 2)->default(0);
            $table->decimal('montant_fixe', 12, 2)->default(1000);
            $table->integer('nb_retards')->default(0);
            $table->decimal('montant_retard', 12, 2)->default(0);
            $table->integer('nb_absences')->default(0);
            $table->decimal('montant_absence', 12, 2)->default(0);
            $table->decimal('acompte', 12, 2)->default(0);
            $table->decimal('pret', 12, 2)->default(0);
            $table->integer('duree_pret')->nullable();
            $table->string('motif_sanction')->nullable();
            $table->integer('duree_sanction')->nullable();
            $table->decimal('montant_sanction', 12, 2)->default(0);
            $table->decimal('imputation_salaire', 12, 2)->default(0);
            $table->decimal('frais_bancaires', 12, 2)->default(0);
            $table->decimal('cnps', 12, 2)->default(0);
            $table->decimal('net_a_payer', 12, 2)->default(0);
            $table->enum('statut', ['brouillon', 'validé', 'payé'])->default('brouillon');
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->unique(['employe_id', 'periode', 'vague']);
        });

        // ABSENCES
        Schema::create('rh_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('rh_employes')->cascadeOnDelete();
            $table->string('reference', 20)->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->date('date_reprise')->nullable();
            $table->integer('nombre_jours')->default(1);
            $table->text('motif')->nullable();
            $table->enum('type_journee', ['journée complète', 'demi-journée'])->default('journée complète');
            $table->enum('type_absence', [
                'Congés', 'Maladie', 'Permission personnelle',
                'Mission', 'Maternité', 'Paternité',
                'Décès', 'Sans solde', 'Autre'
            ])->default('Permission personnelle');
            $table->boolean('justificatif_fourni')->default(false);
            $table->string('justificatif_path')->nullable();
            $table->text('observations')->nullable();
            $table->enum('statut', ['en_attente', 'approuvé', 'refusé'])->default('en_attente');
            $table->string('visa_rh')->nullable();
            $table->timestamps();
        });

        // PRÊTS & ACOMPTES
        Schema::create('rh_prets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('rh_employes')->cascadeOnDelete();
            $table->enum('type', ['pret', 'acompte'])->default('acompte');
            $table->decimal('montant', 12, 2);
            $table->integer('duree_mois')->nullable();
            $table->decimal('mensualite', 12, 2)->nullable();
            $table->decimal('montant_rembourse', 12, 2)->default(0);
            $table->date('date_debut');
            $table->date('date_fin_prevue')->nullable();
            $table->enum('statut', ['en_cours', 'remboursé', 'annulé'])->default('en_cours');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // HEURES SUPPLÉMENTAIRES
        Schema::create('rh_heures_sup', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('rh_employes')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('nb_heures', 5, 2);
            $table->decimal('taux_majoration', 5, 2)->default(1.0);
            $table->decimal('montant', 12, 2)->default(0);
            $table->string('justification')->nullable();
            $table->boolean('valide')->default(false);
            $table->timestamps();
        });

        // SANCTIONS
        Schema::create('rh_sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('rh_employes')->cascadeOnDelete();
            $table->date('date');
            $table->string('motif');
            $table->enum('type', ['avertissement', 'mise_a_pied', 'amende', 'autre'])->default('amende');
            $table->integer('duree_jours')->nullable();
            $table->decimal('montant', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('statut', ['en_attente', 'validé', 'annulé'])->default('en_attente');
            $table->timestamps();
        });

        // RETARDS
        Schema::create('rh_retards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('rh_employes')->cascadeOnDelete();
            $table->date('date');
            $table->time('heure_arrivee')->nullable();
            $table->integer('minutes_retard')->default(0);
            $table->decimal('montant_deduction', 12, 2)->default(0);
            $table->string('justification')->nullable();
            $table->boolean('justifie')->default(false);
            $table->timestamps();
        });

        // RÉCAPITULATIF MENSUEL
        Schema::create('rh_recapitulatifs', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->unique();
            $table->string('mois_annee');
            $table->integer('nb_employes')->default(0);
            $table->decimal('masse_salariale_brute', 15, 2)->default(0);
            $table->decimal('masse_salariale_nette', 15, 2)->default(0);
            $table->decimal('total_sanctions', 12, 2)->default(0);
            $table->decimal('total_acomptes', 12, 2)->default(0);
            $table->decimal('total_heures_sup', 12, 2)->default(0);
            $table->integer('total_absences_jours')->default(0);
            $table->json('details_par_direction')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_recapitulatifs');
        Schema::dropIfExists('rh_retards');
        Schema::dropIfExists('rh_sanctions');
        Schema::dropIfExists('rh_heures_sup');
        Schema::dropIfExists('rh_prets');
        Schema::dropIfExists('rh_absences');
        Schema::dropIfExists('rh_bulletins_paie');
        Schema::dropIfExists('rh_employes');
        Schema::dropIfExists('rh_postes');
        Schema::dropIfExists('rh_services');
        Schema::dropIfExists('rh_directions');
    }
};