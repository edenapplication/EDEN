<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('beneficiaire_paiements')) {
            Schema::create('beneficiaire_paiements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('beneficiaire_id');
                $table->unsignedBigInteger('dossier_client_id');
                $table->string('paiement_type', 20); // 'technique' | 'logistique'
                $table->unsignedBigInteger('paiement_id');
                $table->decimal('montant', 15, 2);
                $table->date('date_paiement');
                $table->string('user_reference', 50)->nullable();
                $table->unsignedBigInteger('bon_id')->nullable();
                $table->timestamps();

                $table->index('beneficiaire_id');
                $table->index('dossier_client_id');
                $table->index('paiement_type');
                $table->index('bon_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiaire_paiements');
    }
};