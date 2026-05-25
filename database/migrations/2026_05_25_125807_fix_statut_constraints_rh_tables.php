<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ✅ SQLite ne supporte pas ALTER COLUMN — on recrée les tables si nécessaire
        // Solution : passer toutes les colonnes statut en TEXT sans CHECK

        if (config('database.default') === 'sqlite') {
            // Pour SQLite : supprimer la contrainte en recréant la colonne via raw
            // On ne peut pas modifier directement, donc on met à jour les données existantes
            // et on laisse Laravel gérer sans CHECK constraint

            // Corriger les valeurs existantes mal écrites
            DB::statement("UPDATE rh_prets SET statut = 'en_cours'  WHERE statut NOT IN ('en_cours','rembourse','annule')");
            DB::statement("UPDATE rh_sanctions SET statut = 'en_attente' WHERE statut NOT IN ('en_attente','valide','annule')");
        }
    }

    public function down(): void {}
};