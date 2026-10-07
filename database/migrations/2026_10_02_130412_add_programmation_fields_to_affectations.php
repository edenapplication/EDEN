<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            if (!Schema::hasColumn('affectations', 'heure_implantation')) {
                $table->time('heure_implantation')->nullable()->after('date_implantation');
            }
            if (!Schema::hasColumn('affectations', 'frais_logistique_paye')) {
                $table->boolean('frais_logistique_paye')->default(false)->after('heure_implantation');
            }
            if (!Schema::hasColumn('affectations', 'statut_presence')) {
                $table->string('statut_presence', 20)
                      ->default('en_attente')
                      ->after('frais_logistique_paye');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            foreach (['heure_implantation', 'frais_logistique_paye', 'statut_presence'] as $col) {
                if (Schema::hasColumn('affectations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};