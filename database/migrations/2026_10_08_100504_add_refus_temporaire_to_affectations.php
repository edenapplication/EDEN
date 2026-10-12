<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            // ✅ Intention de refus (avant validation)
            $table->boolean('refus_en_attente')->default(false)->after('statut_acceptation');
            $table->date('date_refus_prevue')->nullable()->after('refus_en_attente');
            $table->text('motif_refus_prevu')->nullable()->after('date_refus_prevue');
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropColumn(['refus_en_attente', 'date_refus_prevue', 'motif_refus_prevu']);
        });
    }
};