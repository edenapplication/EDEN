<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lot_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')
                  ->constrained('lots')
                  ->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lot_histories'); }
};