<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dossiers_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('commercial_id')->nullable()->constrained('commerciaux')->nullOnDelete();
            $table->foreignId('conducteur_id')->nullable()->constrained('conducteurs')->nullOnDelete();
            $table->foreignId('facilitateur_id')->nullable()->constrained('facilitateurs')->nullOnDelete();
            $table->foreignId('agent_commercial_id')->nullable()->constrained('agents_commerciaux')->nullOnDelete();
            $table->foreignId('grand_site_id')->nullable()->constrained('grand_sites')->nullOnDelete();
            $table->string('direction')->nullable(); // baffoussam | bagante | direction_generale
            $table->decimal('superficie_voulue', 10, 2)->nullable();
            $table->decimal('prix_superficie', 15, 2)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('dossiers_clients'); }
};