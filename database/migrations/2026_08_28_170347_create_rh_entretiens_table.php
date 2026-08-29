<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_entretiens', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('candidat_id')->constrained('rh_candidats')->onDelete('cascade');
            $table->enum('type', ['rh', 'hierarchique', 'technique', 'final']);
            $table->date('date_entretien');
            $table->time('heure')->nullable();
            $table->string('lieu', 255)->nullable();
            $table->string('evaluateur', 150)->nullable();
            $table->string('evaluateur_poste', 150)->nullable();
            
            // ===== ÉVALUATION =====
            $table->integer('note')->nullable(); // 1 à 5
            $table->text('points_forts')->nullable();
            $table->text('points_faibles')->nullable();
            $table->text('remarques')->nullable();
            
            // ===== DÉCISION =====
            $table->enum('decision', ['positif', 'negatif', 'en_attente'])->default('en_attente');
            
            $table->timestamps();
            
            $table->index('candidat_id');
            $table->index('type');
            $table->index('date_entretien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_entretiens');
    }
};