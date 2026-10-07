<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite ne supporte pas dropUnique directement → on recrée la table
        if (DB::getDriverName() === 'sqlite') {
            // Sauvegarde des données
            $affectations = DB::table('affectations')->get();

            Schema::dropIfExists('affectations_temp');

            Schema::create('affectations_temp', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grand_site_id')->nullable();
                $table->unsignedBigInteger('site_id')->nullable();
                $table->unsignedBigInteger('tf_id')->nullable();
                $table->unsignedBigInteger('bloc_id')->nullable();
                $table->unsignedBigInteger('lot_affectation_id');
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('dossier_client_id')->nullable();
                $table->unsignedBigInteger('beneficiaire_id')->nullable();
                $table->date('date_affectation')->nullable();
                $table->string('statut')->default('actif');
                $table->string('statut_acceptation')->default('en_attente');
                $table->string('etape_programmation')->default('nouvelle');
                $table->date('date_acceptation')->nullable();
                $table->date('date_refus')->nullable();
                $table->text('motif_refus')->nullable();
                $table->dateTime('accepte_le')->nullable();
                $table->dateTime('refuse_le')->nullable();
                $table->unsignedBigInteger('accepte_par')->nullable();
                $table->unsignedBigInteger('refuse_par')->nullable();
                $table->dateTime('date_implantation')->nullable();
                $table->time('heure_implantation')->nullable();
                $table->unsignedBigInteger('geometre_id')->nullable();
                $table->boolean('frais_logistique_paye')->default(false);
                $table->string('statut_presence')->default('en_attente');
                $table->text('notes')->nullable();
                $table->timestamps();

                // ✅ AUCUNE contrainte UNIQUE ici !
                $table->index('lot_affectation_id');
                $table->index('statut');
                $table->index('statut_acceptation');
            });

            // Réinsérer les données
            foreach ($affectations as $row) {
                DB::table('affectations_temp')->insert((array) $row);
            }

            Schema::drop('affectations');
            Schema::rename('affectations_temp', 'affectations');
        } else {
            // MySQL / PostgreSQL : drop direct
            Schema::table('affectations', function (Blueprint $table) {
                $table->dropUnique(['lot_affectation_id', 'statut']);
            });
        }
    }

    public function down(): void
    {
        // Pas de rollback
    }
};