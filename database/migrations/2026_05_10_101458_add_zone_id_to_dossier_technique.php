<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dossiers_techniques', function (Blueprint $table) {
            $table->foreignId('zone_groupe_id')->nullable()->after('lot_id')
                  ->constrained('zone_groupes')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('dossiers_techniques', function (Blueprint $table) {
            $table->dropForeign(['zone_groupe_id']);
            $table->dropColumn('zone_groupe_id');
        });
    }
};