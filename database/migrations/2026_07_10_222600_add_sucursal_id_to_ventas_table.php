<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Agregamos la columna sucursal_id y la relacionamos con la tabla sucursales
            $table->foreignId('sucursal_id')->nullable()->after('cliente_id')->constrained('sucursales')->nullOnDelete();
            
            // Aprovechamos para asegurarnos de que existan estas otras columnas que manda tu controlador
            if (!Schema::hasColumn('ventas', 'corte_caja_id')) {
                $table->foreignId('corte_caja_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('ventas', 'requiere_factura')) {
                $table->boolean('requiere_factura')->default(false)->after('estado');
            }
            if (!Schema::hasColumn('ventas', 'rfc_cliente')) {
                $table->string('rfc_cliente')->nullable()->after('requiere_factura');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['sucursal_id', 'corte_caja_id', 'requiere_factura', 'rfc_cliente']);
        });
    }
};