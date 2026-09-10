<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            // ✅ Objet = simple chaîne (valeurs définies côté modèle)
            $table->string('objet')->nullable()->after('type_personne');

            // ✅ Motif (utilisé uniquement pour certains objets)
            $table->string('motif')->nullable()->after('objet');
        });

        // ✅ On revient aux 3 types d'origine
        // (à supprimer si la colonne type_personne n'est PAS un ENUM)
        DB::statement("ALTER TABLE visites MODIFY type_personne ENUM('client','proprietaire','autre') DEFAULT 'autre'");
        DB::statement("ALTER TABLE visiteurs MODIFY type ENUM('client','proprietaire','autre') DEFAULT 'autre'");
    }

    public function down(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            $table->dropColumn(['objet', 'motif']);
        });
    }
};