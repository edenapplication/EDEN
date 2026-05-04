<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tf_id')
                  ->constrained('tfs')
                  ->cascadeOnDelete();
            $table->foreignId('client_id')
                  ->nullable()
                  ->constrained('clients')
                  ->nullOnDelete();
            $table->foreignId('commercial_id')
                  ->nullable()
                  ->constrained('commerciaux')
                  ->nullOnDelete();
            $table->foreignId('conducteur_id')
                  ->nullable()
                  ->constrained('conducteurs')
                  ->nullOnDelete();
            $table->foreignId('facilitateur_id')
                  ->nullable()
                  ->constrained('facilitateurs')
                  ->nullOnDelete();

            $table->string('svg_id');
            $table->string('code');
            $table->string('origine')->nullable();  // eden | famille
            $table->string('type')->nullable();      // deja_implante | implantation_prevue | dossier_technique | morcellement
            $table->string('color')->nullable();
            $table->string('owner_name')->nullable();

            $table->decimal('superficie', 10, 2)->nullable();
            $table->decimal('prix', 15, 2)->nullable();

            $table->date('date_prevue')->nullable();
            $table->date('date_confirmee')->nullable();
            $table->date('date_morcellement')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lots'); }
};