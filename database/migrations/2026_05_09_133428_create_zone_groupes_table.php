<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zone_groupes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tf_id')->constrained('tfs')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('dossier_client_id')->nullable()->constrained('dossiers_clients')->nullOnDelete();
            $table->string('nom')->nullable();
            $table->string('owner_name')->nullable();
            $table->json('points');           // tableau de points SVG [{x,y}, ...]
            $table->json('lot_ids')->nullable(); // ids des lots inclus
            $table->decimal('superficie_totale', 12, 2)->default(0);
            $table->string('type')->nullable(); // implantation_prevue, deja_implante, dossier_technique, morcellement
            $table->date('date_prevue')->nullable();
            $table->date('date_confirmee')->nullable();
            $table->date('date_morcellement')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_groupes');
    }
};