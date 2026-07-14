<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->decimal('cargos_extra', 10, 2)->default(0)->after('total');
            $table->string('motivo_cargos_extra')->nullable()->after('cargos_extra');
        });
    }

    public function down(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropColumn(['cargos_extra', 'motivo_cargos_extra']);
        });
    }
};