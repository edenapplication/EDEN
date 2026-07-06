<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('feb_utilisateurs', function (Blueprint $table) {
            $table->string('direction')->nullable()->after('poste');
            $table->string('service')->nullable()->after('direction');
        });
    }
    public function down(): void
    {
        Schema::table('feb_utilisateurs', function (Blueprint $table) {
            $table->dropColumn(['direction','service']);
        });
    }
};