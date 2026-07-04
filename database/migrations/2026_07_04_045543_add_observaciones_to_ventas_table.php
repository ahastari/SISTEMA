<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Agregamos la columna 'observaciones', permitiendo que sea nula
            $table->text('observaciones')->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Eliminamos la columna si hacemos un rollback
            $table->dropColumn('observaciones');
        });
    }
};