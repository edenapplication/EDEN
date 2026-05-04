<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dossier_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')
                  ->constrained('dossiers_techniques')
                  ->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('dossier_files'); }
};