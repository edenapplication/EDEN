<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rh_conges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employe_id');
            $table->date('date_debut');
            $table->date('date_fin');          // toujours date_debut + 14 jours
            $table->integer('nb_jours')->default(15);
            $table->string('motif')->nullable();
            $table->string('statut', 20)->default('planifie'); // planifie, en_cours, termine, annule
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employe_id')->references('id')->on('rh_employes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_conges');
    }
};