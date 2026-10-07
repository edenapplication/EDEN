<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            if (!Schema::hasColumn('affectations', 'date_implantation')) {
                $table->dateTime('date_implantation')->nullable()->after('date_acceptation');
            }
            if (!Schema::hasColumn('affectations', 'geometre_id')) {
                $table->unsignedBigInteger('geometre_id')->nullable()->after('date_implantation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            if (Schema::hasColumn('affectations', 'date_implantation')) $table->dropColumn('date_implantation');
            if (Schema::hasColumn('affectations', 'geometre_id'))       $table->dropColumn('geometre_id');
        });
    }
};