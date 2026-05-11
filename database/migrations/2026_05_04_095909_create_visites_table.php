<?php
// database/migrations/create_visites_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visiteurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('numero')->nullable();
            $table->string('type')->default('autre'); // client | proprietaire | autre
            $table->timestamps();
        });

        Schema::create('visites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visiteur_id')->constrained('visiteurs')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('dossier_client_id')->nullable()->constrained('dossiers_clients')->nullOnDelete();
            $table->foreignId('grand_site_id')->nullable()->constrained('grand_sites')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->date('date_visite');
            $table->time('heure_arrivee')->nullable();
            $table->time('heure_depart')->nullable();
            $table->string('type_personne')->default('autre'); // client | proprietaire | autre
            $table->text('note')->nullable();
            $table->boolean('paiement_lie')->default(false);
            $table->foreignId('paiement_id')->nullable()->constrained('paiements_dossier')->nullOnDelete();
            $table->timestamps();

            // Une même personne ne peut pas venir deux fois le même jour
            $table->unique(['visiteur_id', 'date_visite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visites');
        Schema::dropIfExists('visiteurs');
    }
};