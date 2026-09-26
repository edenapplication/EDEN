<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beneficiaires', function (Blueprint $table) {
            $table->id();

            // 🔗 Lien vers le dossier principal
            $table->foreignId('dossier_client_id')
                  ->constrained('dossiers_clients')
                  ->cascadeOnDelete();

            // 👤 Informations du bénéficiaire
            $table->string('nom');                          // Nom et prénom(s) — obligatoire
            $table->string('telephone')->nullable();        // Téléphone
            $table->string('cni_path');                     // CNI obligatoire (chemin storage)
            $table->string('lots_texte')->nullable();       // Lot(s) — texte libre

            // 📐 Superficie attribuée (m²)
            $table->decimal('superficie_attribuee', 12, 2);

            // 📝 Notes optionnelles
            $table->text('notes')->nullable();

            $table->timestamps();

            // Index pour accélérer les jointures
            $table->index('dossier_client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaires');
    }
};