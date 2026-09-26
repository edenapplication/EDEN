<?php
// database/migrations/xxxx_xx_xx_create_historique_affectations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historique_affectations', function (Blueprint $table) {
            $table->id();

            // 🔗 Lien vers le dossier concerné
            $table->foreignId('dossier_client_id')
                  ->constrained('dossiers_clients')
                  ->cascadeOnDelete();

            // 📌 Type d'action : affectation_lot | modification_beneficiaire | suppression_beneficiaire | ajout_beneficiaire | annulation_affectation
            $table->string('type_action', 60);

            // 👤 Utilisateur ayant fait l'action
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            // 🏷️ Cible (bénéficiaire, lot, etc.)
            $table->string('cible_type', 60)->nullable(); // beneficiaire | lot
            $table->unsignedBigInteger('cible_id')->nullable();

            // 📝 Résumé lisible
            $table->string('resume', 500);

            // 📋 Données avant / après (JSON)
            $table->json('donnees_avant')->nullable();
            $table->json('donnees_apres')->nullable();

            $table->timestamps();

            $table->index(['dossier_client_id', 'created_at']);
            $table->index(['type_action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_affectations');
    }
};