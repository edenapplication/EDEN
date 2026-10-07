<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // ─────────────────────────────────────────────────────
            // SQLite : on recrée la table sans contrainte UNIQUE
            // ─────────────────────────────────────────────────────

            // 1. Sauvegarde des données
            $affectations = DB::table('affectations')->get();

            // 2. Suppression de la table temporaire si elle existe déjà
            Schema::dropIfExists('affectations_temp');

            // 3. Création de la table temporaire (sans UNIQUE)
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
            });

            // ✅ Index avec NOMS EXPLICITES (préfixés 'aff_temp_')
            //    pour éviter toute collision avec les anciens index
            DB::statement('CREATE INDEX IF NOT EXISTS aff_temp_lot_idx ON affectations_temp (lot_affectation_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS aff_temp_statut_idx ON affectations_temp (statut)');
            DB::statement('CREATE INDEX IF NOT EXISTS aff_temp_stat_acc_idx ON affectations_temp (statut_acceptation)');
            DB::statement('CREATE INDEX IF NOT EXISTS aff_temp_etape_idx ON affectations_temp (etape_programmation)');
            DB::statement('CREATE INDEX IF NOT EXISTS aff_temp_date_impl_idx ON affectations_temp (date_implantation)');
            DB::statement('CREATE INDEX IF NOT EXISTS aff_temp_benef_idx ON affectations_temp (beneficiaire_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS aff_temp_dossier_idx ON affectations_temp (dossier_client_id)');

            // 4. Réinsertion des données
            foreach ($affectations as $row) {
                DB::table('affectations_temp')->insert((array) $row);
            }

            // 5. Remplacement de l'ancienne table
            Schema::drop('affectations');
            Schema::rename('affectations_temp', 'affectations');

            // 6. Renommage des index pour qu'ils portent le nom final
            DB::statement('DROP INDEX IF EXISTS aff_temp_lot_idx');
            DB::statement('DROP INDEX IF EXISTS aff_temp_statut_idx');
            DB::statement('DROP INDEX IF EXISTS aff_temp_stat_acc_idx');
            DB::statement('DROP INDEX IF EXISTS aff_temp_etape_idx');
            DB::statement('DROP INDEX IF EXISTS aff_temp_date_impl_idx');
            DB::statement('DROP INDEX IF EXISTS aff_temp_benef_idx');
            DB::statement('DROP INDEX IF EXISTS aff_temp_dossier_idx');

            DB::statement('CREATE INDEX IF NOT EXISTS affectations_lot_affectation_id_index ON affectations (lot_affectation_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS affectations_statut_index ON affectations (statut)');
            DB::statement('CREATE INDEX IF NOT EXISTS affectations_statut_acceptation_index ON affectations (statut_acceptation)');
            DB::statement('CREATE INDEX IF NOT EXISTS affectations_etape_programmation_index ON affectations (etape_programmation)');
            DB::statement('CREATE INDEX IF NOT EXISTS affectations_date_implantation_index ON affectations (date_implantation)');
            DB::statement('CREATE INDEX IF NOT EXISTS affectations_beneficiaire_id_index ON affectations (beneficiaire_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS affectations_dossier_client_id_index ON affectations (dossier_client_id)');

        } else {
            // MySQL / PostgreSQL
            try {
                Schema::table('affectations', function (Blueprint $table) {
                    $table->dropUnique(['lot_affectation_id', 'statut']);
                });
            } catch (\Exception $e) {
                Log::warning('UNIQUE déjà absent : ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // Pas de rollback
    }
};