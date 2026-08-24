<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bons_paiement', function (Blueprint $table) {
            // ✅ Ajouter les colonnes si elles n'existent pas
            if (!Schema::hasColumn('bons_paiement', 'user_reference')) {
                $table->string('user_reference')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('bons_paiement', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('user_reference');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('bons_paiement', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_reference', 'user_id']);
        });
    }
};