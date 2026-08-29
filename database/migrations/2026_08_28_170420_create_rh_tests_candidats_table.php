<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_tests_candidats', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('candidat_id')->constrained('rh_candidats')->onDelete('cascade');
            $table->string('type_test', 100);
            $table->date('date_test');
            $table->decimal('note', 5, 2)->nullable();
            $table->text('resultats')->nullable();
            $table->text('appreciation')->nullable();
            $table->string('fichier_path')->nullable();
            
            $table->timestamps();
            
            $table->index('candidat_id');
            $table->index('date_test');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_tests_candidats');
    }
};