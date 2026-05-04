<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            if (!Schema::hasColumn('lots', 'dossier_client_id')) {
                $table->foreignId('dossier_client_id')
                      ->nullable()
                      ->constrained('dossiers_clients')
                      ->nullOnDelete()
                      ->after('client_id');
            }
        });
    }
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\DossierClient::class, 'dossier_client_id');
        });
    }
};