<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grand_site_id')
                  ->nullable()
                  ->constrained('grand_sites')
                  ->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('svg_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sites'); }
};