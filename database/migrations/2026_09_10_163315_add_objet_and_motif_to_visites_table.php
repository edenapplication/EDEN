<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Ajout des colonnes objet et motif (string — compatible SQLite/MySQL)
        if (!Schema::hasColumn('visites', 'objet')) {
            Schema::table('visites', function (Blueprint $table) {
                $table->string('objet')->nullable()->after('type_personne');
            });
        }

        if (!Schema::hasColumn('visites', 'motif')) {
            Schema::table('visites', function (Blueprint $table) {
                $table->string('motif')->nullable()->after('objet');
            });
        }

        // ✅ Nettoyage des anciennes valeurs (au cas où)
        DB::table('visites')
            ->whereIn('type_personne', ['descente_client', 'visiteur'])
            ->update(['type_personne' => 'autre']);

        DB::table('visiteurs')
            ->whereIn('type', ['descente_client', 'visiteur'])
            ->update(['type' => 'autre']);

        // ✅ Restriction ENUM uniquement sur MySQL / MariaDB
        // (sur SQLite la colonne est déjà en varchar → rien à faire)
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE visites MODIFY type_personne ENUM('client','proprietaire','autre') DEFAULT 'autre'");
            DB::statement("ALTER TABLE visiteurs MODIFY type ENUM('client','proprietaire','autre') DEFAULT 'autre'");
        }
    }

    public function down(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            if (Schema::hasColumn('visites', 'objet')) {
                $table->dropColumn('objet');
            }
            if (Schema::hasColumn('visites', 'motif')) {
                $table->dropColumn('motif');
            }
        });
    }
};