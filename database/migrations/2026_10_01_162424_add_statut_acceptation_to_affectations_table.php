<?php
// database/migrations/XXXX_add_statut_acceptation_to_affectations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            if (!Schema::hasColumn('affectations', 'statut_acceptation')) {
                $table->string('statut_acceptation', 20)->default('en_attente')->after('statut');
                $table->text('motif_refus')->nullable()->after('statut_acceptation');
                $table->timestamp('accepte_le')->nullable()->after('motif_refus');
                $table->timestamp('refuse_le')->nullable()->after('accepte_le');
                $table->unsignedBigInteger('accepte_par')->nullable()->after('refuse_le');
                $table->unsignedBigInteger('refuse_par')->nullable()->after('accepte_par');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropColumn([
                'statut_acceptation',
                'motif_refus',
                'accepte_le',
                'refuse_le',
                'accepte_par',
                'refuse_par',
            ]);
        });
    }
};