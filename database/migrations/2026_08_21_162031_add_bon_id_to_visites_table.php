<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('visites', function (Blueprint $table) {
            if (!Schema::hasColumn('visites', 'bon_id')) {
                $table->foreignId('bon_id')
                      ->nullable()
                      ->after('paiement_lie')
                      ->constrained('bons_paiement')
                      ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('visites', function (Blueprint $table) {
            $table->dropForeign(['bon_id']);
            $table->dropColumn('bon_id');
        });
    }
};