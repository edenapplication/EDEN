<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // SQLite ne supporte pas dropNotNull directement
        // On recrée la table si SQLite
        if (config('database.default') === 'sqlite') {
            // Vérifier si la colonne est déjà nullable via une tentative d'insert test
            // La solution propre : recréer la table avec lot_id nullable
            Schema::table('dossiers_techniques', function (Blueprint $table) {
                // Sur SQLite, change() fonctionne avec doctrine/dbal installé
                // Si pas installé : composer require doctrine/dbal
                $table->unsignedBigInteger('lot_id')->nullable()->change();
            });
        }
    }

    public function down(): void {}
};