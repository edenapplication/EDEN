<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->json('filtres')->nullable(); // stocke les paramètres du filtre
            $table->string('fichier_pdf')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rapports'); }
};