<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_pdf', function (Blueprint $table) {
            $table->id();

            // ✅ On accepte TOUS les types possibles
            $table->enum('type', [
                'programmation_initiale',
                'programmation_active',
                'programmation_finale',
                'rapport_programmation',
                'rapport_programmation_date',
            ]);

            $table->date('date_semaine');
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->integer('nb_lignes')->default(0);
            $table->integer('nb_lots')->default(0);
            $table->decimal('superficie_totale', 12, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'date_semaine']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_pdf');
    }
};