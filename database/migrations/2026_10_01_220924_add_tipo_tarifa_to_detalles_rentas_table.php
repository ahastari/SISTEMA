<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarda la tarifa vigente al momento de rentar (dia | m2).
        // Las rentas ya existentes se quedan como 'dia', que es como se calcularon.
        Schema::table('detalles_rentas', function (Blueprint $table) {
            $table->string('tipo_tarifa', 10)->default('dia')->after('precio_dia');
        });
    }

    public function down(): void
    {
        Schema::table('detalles_rentas', function (Blueprint $table) {
            $table->dropColumn('tipo_tarifa');
        });
    }
};