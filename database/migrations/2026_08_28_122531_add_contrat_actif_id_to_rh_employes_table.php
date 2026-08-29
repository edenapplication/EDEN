<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rh_employes', function (Blueprint $table) {
            $table->foreignId('contrat_actif_id')->nullable()->after('id')
                  ->constrained('rh_contrats')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rh_employes', function (Blueprint $table) {
            $table->dropForeign(['contrat_actif_id']);
            $table->dropColumn('contrat_actif_id');
        });
    }
};