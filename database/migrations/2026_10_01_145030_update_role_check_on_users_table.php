<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ⚠️ SQLite ne supporte pas la modification directe des CHECK.
        // Il faut recréer la colonne via une table temporaire.

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // 1. Créer une nouvelle table temporaire avec la nouvelle contrainte
            DB::statement('
                CREATE TABLE users_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    name VARCHAR NOT NULL,
                    email VARCHAR NOT NULL UNIQUE,
                    email_verified_at DATETIME,
                    password VARCHAR NOT NULL,
                    role VARCHAR NOT NULL CHECK (role IN (\'admin\', \'rh\', \'commercial\', \'geometre\')),
                    actif TINYINT(1) NOT NULL DEFAULT 1,
                    reference VARCHAR,
                    remember_token VARCHAR,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');

            // 2. Copier les données
            DB::statement('
                INSERT INTO users_new (id, name, email, email_verified_at, password, role, actif, reference, remember_token, created_at, updated_at)
                SELECT id, name, email, email_verified_at, password, role, actif, reference, remember_token, created_at, updated_at
                FROM users
            ');

            // 3. Supprimer l'ancienne table
            Schema::drop('users');

            // 4. Renommer la nouvelle
            DB::statement('ALTER TABLE users_new RENAME TO users');
        } else {
            // Pour MySQL/PostgreSQL, modifier directement la colonne
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL");
            DB::statement("ALTER TABLE users DROP CHECK users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'rh', 'commercial', 'geometre'))");
        }
    }

    public function down(): void
    {
        // Pas de rollback propre possible sans perdre la contrainte
        // On laisse tel quel
    }
};