<?php
// database/migrations/XXXX_add_date_acceptation_and_refus_to_affectations.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            if (!Schema::hasColumn('affectations', 'date_acceptation')) {
                $table->date('date_acceptation')->nullable()->after('motif_refus');
            }
            if (!Schema::hasColumn('affectations', 'date_refus')) {
                $table->date('date_refus')->nullable()->after('date_acceptation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            if (Schema::hasColumn('affectations', 'date_acceptation')) {
                $table->dropColumn('date_acceptation');
            }
            if (Schema::hasColumn('affectations', 'date_refus')) {
                $table->dropColumn('date_refus');
            }
        });
    }
};