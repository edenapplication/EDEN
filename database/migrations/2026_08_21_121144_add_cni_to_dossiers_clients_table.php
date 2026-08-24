<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            $table->json('cni_images')->nullable()->after('direction');
        });
    }
    public function down(): void
    {
        Schema::table('dossiers_clients', function (Blueprint $table) {
            $table->dropColumn('cni_images');
        });
    }
};