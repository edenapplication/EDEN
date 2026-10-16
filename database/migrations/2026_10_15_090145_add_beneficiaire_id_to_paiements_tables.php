<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('paiements_techniques', 'beneficiaire_id')) {
            Schema::table('paiements_techniques', function (Blueprint $table) {
                $table->unsignedBigInteger('beneficiaire_id')->nullable()->after('dossier_client_id');
                $table->index('beneficiaire_id');
            });
        }

        if (!Schema::hasColumn('paiements_logistiques', 'beneficiaire_id')) {
            Schema::table('paiements_logistiques', function (Blueprint $table) {
                $table->unsignedBigInteger('beneficiaire_id')->nullable()->after('dossier_client_id');
                $table->index('beneficiaire_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('paiements_techniques', 'beneficiaire_id')) {
            Schema::table('paiements_techniques', function (Blueprint $table) {
                $table->dropIndex(['beneficiaire_id']);
                $table->dropColumn('beneficiaire_id');
            });
        }

        if (Schema::hasColumn('paiements_logistiques', 'beneficiaire_id')) {
            Schema::table('paiements_logistiques', function (Blueprint $table) {
                $table->dropIndex(['beneficiaire_id']);
                $table->dropColumn('beneficiaire_id');
            });
        }
    }
};