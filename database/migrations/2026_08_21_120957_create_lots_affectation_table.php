<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lots_affectation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grand_site_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('tf_id')->nullable();
            $table->unsignedBigInteger('bloc_id');
            $table->string('numero', 50); // Ex: 01, 02, 15A
            $table->decimal('superficie', 10, 2)->nullable();
            $table->boolean('disponible')->default(true);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->foreign('grand_site_id')->references('id')->on('grand_sites')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->foreign('tf_id')->references('id')->on('tfs')->onDelete('cascade');
            $table->foreign('bloc_id')->references('id')->on('blocs')->onDelete('cascade');
            $table->unique(['bloc_id','numero']);
        });
    }
    public function down(): void { Schema::dropIfExists('lots_affectation'); }
};