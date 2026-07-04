<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            $table->decimal('prix_technique',   12, 2)->default(0)->after('prix_superficie');
            $table->decimal('prix_morcellement',12, 2)->default(0)->after('prix_technique');
        });
    }
    public function down(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            $table->dropColumn(['prix_technique','prix_morcellement']);
        });
    }
};