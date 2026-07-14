<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            // Campo para que el gerente defina cuánto cobrar por día de retraso
            $table->decimal('penalizacion_diaria', 10, 2)->default(0)->after('activa');
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn('penalizacion_diaria');
        });
    }
};