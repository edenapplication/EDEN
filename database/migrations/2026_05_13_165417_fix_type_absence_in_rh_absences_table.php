<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rh_absences', function (Blueprint $table) {
            $table->string('type_absence', 100)->change();
        });
    }
    public function down(): void
    {
        Schema::table('rh_absences', function (Blueprint $table) {
            $table->string('type_absence')->change();
        });
    }
};