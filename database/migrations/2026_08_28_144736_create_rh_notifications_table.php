<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rh_notifications', function (Blueprint $table) {
            $table->id();
            
            // ===== LIENS =====
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('alerte_id')->nullable()->constrained('rh_alertes')->onDelete('set null');
            
            // ===== CONTENU =====
            $table->string('canal', 50); // email, interface, sms
            $table->string('sujet', 255);
            $table->text('message');
            $table->boolean('envoyee')->default(false);
            $table->timestamp('date_envoi')->nullable();
            $table->text('erreur')->nullable();
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('envoyee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_notifications');
    }
};