<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            if (!Schema::hasColumn('dossiers_clients', 'prix_logistique')) {
                $table->decimal('prix_logistique', 12, 2)->default(0)->after('prix_morcellement');
            }
        });
    }
    public function down(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            $table->dropColumn('prix_logistique');
        });
    }
};