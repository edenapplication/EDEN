<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rh_employe_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employe_id')->constrained('rh_employes')->cascadeOnDelete();
    $table->string('nom');
    $table->enum('type', ['cv', 'plan_localisation', 'cni', 'diplome', 'contrat', 'autre'])->default('autre');
    $table->string('fichier_path');
    $table->string('fichier_nom');
    $table->bigInteger('fichier_taille')->nullable();
    $table->text('description')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rh_employe_documents');
    }
};
