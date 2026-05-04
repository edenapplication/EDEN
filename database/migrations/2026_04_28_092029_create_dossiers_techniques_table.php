<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dossiers_techniques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')
                  ->constrained('lots')
                  ->cascadeOnDelete();
            $table->string('statut')->default('none'); // none | en_cours | complet
            $table->integer('progression')->default(0);
            $table->date('date_sortie')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('dossiers_techniques'); }
};