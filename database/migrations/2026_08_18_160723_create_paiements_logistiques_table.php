<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paiements_logistiques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dossier_client_id');
            $table->decimal('montant', 12, 2);
            $table->date('date_paiement');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->foreign('dossier_client_id')->references('id')
                  ->on('dossiers_clients')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('paiements_logistiques'); }
};