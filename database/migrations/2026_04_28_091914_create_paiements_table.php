<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')
                  ->constrained('lots')
                  ->cascadeOnDelete();
            $table->foreignId('client_id')
                  ->nullable()
                  ->constrained('clients')
                  ->nullOnDelete();
            $table->decimal('montant', 15, 2);
            $table->decimal('reste', 15, 2)->default(0);
            $table->date('date_paiement');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('paiements'); }
};