<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rapports_logistiques', function (Blueprint $table) {
            // ✅ Frais de transport et reste à reverser
            $table->decimal('frais_transport', 15, 2)->default(0)->after('total_global_encaisse');
            $table->decimal('reste_a_reverser', 15, 2)->default(0)->after('frais_transport');
        });
    }

    public function down()
    {
        Schema::table('rapports_logistiques', function (Blueprint $table) {
            $table->dropColumn(['frais_transport', 'reste_a_reverser']);
        });
    }
};