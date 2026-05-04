<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            if (!Schema::hasColumn('dossiers_clients', 'nom_dossier')) {
                $table->string('nom_dossier')->nullable()->after('client_id');
            }
        });
    }
    public function down(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            $table->dropColumn('nom_dossier');
        });
    }
};