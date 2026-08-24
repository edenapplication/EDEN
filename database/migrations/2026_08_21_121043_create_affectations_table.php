<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grand_site_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('tf_id')->nullable();
            $table->unsignedBigInteger('bloc_id');
            $table->unsignedBigInteger('lot_affectation_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('dossier_client_id');
            $table->date('date_affectation');
            $table->string('statut', 30)->default('actif'); // actif | annule
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('grand_site_id')->references('id')->on('grand_sites')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->foreign('tf_id')->references('id')->on('tfs')->onDelete('cascade');
            $table->foreign('bloc_id')->references('id')->on('blocs')->onDelete('cascade');
            $table->foreign('lot_affectation_id')->references('id')->on('lots_affectation')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('dossier_client_id')->references('id')->on('dossiers_clients')->onDelete('cascade');

            // Un lot ne peut être affecté qu'une seule fois activement
            $table->unique(['lot_affectation_id','statut']);
        });
    }
    public function down(): void { Schema::dropIfExists('affectations'); }
};