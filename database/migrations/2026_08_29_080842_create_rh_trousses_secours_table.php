<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_trousses_secours', function (Blueprint $table) {
            $table->id();
            
            // ===== INFORMATIONS =====
            $table->string('localisation', 255);
            $table->string('responsable', 150)->nullable();
            $table->date('date_verification');
            $table->date('prochaine_verification')->nullable();
            $table->text('contenu')->nullable();
            
            // ===== DOCUMENTS =====
            $table->string('inventaire_path')->nullable();
            
            // ===== STATUT =====
            $table->enum('statut', ['ok', 'alerte', 'vide'])->default('ok');
            
            $table->timestamps();
            
            // ===== INDEX =====
            $table->index('date_verification');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_trousses_secours');
    }
};