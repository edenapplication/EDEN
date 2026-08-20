<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bons_paiement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dossier_client_id');
            $table->string('numero_bon')->unique(); // EDG-BON-XXXX
            $table->date('date_bon');

            // Versements du jour
            $table->decimal('versement_dossier',     12, 2)->default(0);
            $table->decimal('versement_technique',   12, 2)->default(0);
            $table->decimal('versement_logistique',  12, 2)->default(0);
            $table->decimal('versement_morcellement',12, 2)->default(0);

            // Totaux cumulés au moment du bon
            $table->decimal('total_dossier_cumul',     12, 2)->default(0);
            $table->decimal('total_technique_cumul',   12, 2)->default(0);
            $table->decimal('total_logistique_cumul',  12, 2)->default(0);
            $table->decimal('total_morcellement_cumul',12, 2)->default(0);

            $table->boolean('afficher_reste')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('dossier_client_id')
                  ->references('id')->on('dossiers_clients')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bons_paiement');
    }
};