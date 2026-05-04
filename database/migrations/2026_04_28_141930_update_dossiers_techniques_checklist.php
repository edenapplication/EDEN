<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dossiers_techniques', function (Blueprint $table) {

            // ✅ checklist — chaque étape cochée ou non
            if (!Schema::hasColumn('dossiers_techniques', 'montage_dossier')) {
                $table->boolean('montage_dossier')->default(false); // 40%
            }
            if (!Schema::hasColumn('dossiers_techniques', 'bon_pour_ccp')) {
                $table->boolean('bon_pour_ccp')->default(false);    // 10%
            }
            if (!Schema::hasColumn('dossiers_techniques', 'controle')) {
                $table->boolean('controle')->default(false);        // 15%
            }
            if (!Schema::hasColumn('dossiers_techniques', 'mise_a_jour')) {
                $table->boolean('mise_a_jour')->default(false);     // 10%
            }
            if (!Schema::hasColumn('dossiers_techniques', 'secretariat')) {
                $table->boolean('secretariat')->default(false);     // 10%
            }
            if (!Schema::hasColumn('dossiers_techniques', 'signature')) {
                $table->boolean('signature')->default(false);       // 15%
            }
        });
    }

    public function down(): void
    {
        Schema::table('dossiers_techniques', function (Blueprint $table) {
            $table->dropColumn([
                'montage_dossier', 'bon_pour_ccp', 'controle',
                'mise_a_jour', 'secretariat', 'signature'
            ]);
        });
    }
};