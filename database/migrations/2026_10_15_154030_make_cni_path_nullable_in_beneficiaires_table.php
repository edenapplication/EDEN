<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ⚠️ On sauvegarde les données (au cas où)
        $beneficiaires = DB::table('beneficiaires')->get();

        // ⚠️ Vérifier que la table existe avant de la recréer
        Schema::dropIfExists('beneficiaires');

        // ✅ Recréer avec TOUTES les colonnes + cni_path nullable
        Schema::create('beneficiaires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dossier_client_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('nom');
            $table->string('telephone')->nullable();
            $table->string('cni_path')->nullable();   // ✅ NULLABLE
            $table->string('lots_texte')->nullable(); // ✅ AJOUTÉ
            $table->decimal('superficie_attribuee', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('etape_actuelle')->nullable();
            $table->timestamp('implantation_prevue')->nullable();
            $table->timestamp('deja_implante')->nullable();
            $table->timestamp('dossier_technique')->nullable();
            $table->timestamp('morcellement')->nullable();
            $table->timestamps();
        });

        // ✅ Réinsérer les données (uniquement les colonnes qui existent dans la nouvelle table)
        foreach ($beneficiaires as $b) {
            $data = (array) $b;

            // Garder uniquement les colonnes de la nouvelle table
            $colonnesValides = [
                'id', 'dossier_client_id', 'client_id', 'nom', 'telephone',
                'cni_path', 'lots_texte', 'superficie_attribuee', 'notes',
                'etape_actuelle', 'implantation_prevue', 'deja_implante',
                'dossier_technique', 'morcellement', 'created_at', 'updated_at'
            ];

            $dataFiltre = array_intersect_key($data, array_flip($colonnesValides));

            DB::table('beneficiaires')->insert($dataFiltre);
        }
    }

    public function down(): void
    {
        // Ne rien faire (pour éviter d'autres pertes)
    }
};