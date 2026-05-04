<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            if (!Schema::hasColumn('lots', 'reserved_client_id')) {
                $table->foreignId('reserved_client_id')
                      ->nullable()
                      ->constrained('clients')
                      ->nullOnDelete();
            }
        });
    }
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropForeign(['reserved_client_id']);
            $table->dropColumn('reserved_client_id');
        });
    }
};