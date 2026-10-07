<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->enum('etape_programmation', [
                'nouvelle',
                'date_attribuee',
                'programmee',
                'finalisee',
            ])->default('nouvelle')->after('statut_acceptation');
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropColumn('etape_programmation');
        });
    }
};