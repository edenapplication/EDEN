<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rapports_logistiques', function (Blueprint $table) {
            $table->id();
            $table->string('titre')->nullable();
            $table->text('note')->nullable();
            $table->string('type')->default('pdf'); // pdf ou excel
            $table->string('chemin_fichier');
            $table->string('nom_fichier');
            $table->unsignedInteger('nb_dossiers')->default(0);
            $table->unsignedInteger('nb_beneficiaires')->default(0);
            $table->decimal('total_prix', 15, 2)->default(0);
            $table->decimal('total_paye', 15, 2)->default(0);
            $table->decimal('total_reste', 15, 2)->default(0);
            $table->decimal('total_global_encaisse', 15, 2)->default(0);
            $table->json('filtres')->nullable(); // filtres appliqués au moment de la génération
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rapports_logistiques');
    }
};