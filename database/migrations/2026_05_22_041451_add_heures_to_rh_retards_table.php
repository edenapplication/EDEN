<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rh_retards', function (Blueprint $table) {
            
            $table->string('heure_depart',  5)->nullable()->after('heure_arrivee');
            
            $table->integer('minutes_sup')->default(0)->after('minutes_retard');
        });
    }
    public function down(): void
    {
        Schema::table('rh_retards', function (Blueprint $table) {
            $table->dropColumn(['heure_depart', 'minutes_sup']);
        });
    }
};