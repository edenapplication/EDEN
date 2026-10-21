<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            $table->enum('sexe', ['masculin', 'feminin'])
                  ->nullable()
                  ->after('telephone');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            $table->dropColumn('sexe');
        });
    }
};