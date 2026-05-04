<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')
                  ->constrained('sites')
                  ->cascadeOnDelete();
            $table->string('svg_zone_id');
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('color')->nullable();
            $table->string('status')->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tfs'); }
};