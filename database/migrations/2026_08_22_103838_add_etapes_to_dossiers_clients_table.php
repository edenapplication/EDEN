<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            $table->string('etape_actuelle', 30)->nullable()->after('nom_dossier');
            // Dates de chaque étape
            $table->date('date_implantation_prevue')->nullable()->after('etape_actuelle');
            $table->date('date_deja_implante')->nullable();
            $table->date('date_dossier_technique')->nullable();
            $table->date('date_morcellement')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            $table->dropColumn([
                'etape_actuelle',
                'date_implantation_prevue',
                'date_deja_implante',
                'date_dossier_technique',
                'date_morcellement',
            ]);
        });
    }
};