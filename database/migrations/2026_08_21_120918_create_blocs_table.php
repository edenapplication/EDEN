<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blocs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grand_site_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('tf_id')->nullable();
            $table->string('code', 50); // Ex: A, B, C
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->foreign('grand_site_id')->references('id')->on('grand_sites')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->foreign('tf_id')->references('id')->on('tfs')->onDelete('cascade');
            $table->unique(['tf_id','code']);
        });
    }
    public function down(): void { Schema::dropIfExists('blocs'); }
};