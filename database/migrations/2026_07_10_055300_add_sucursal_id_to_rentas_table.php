<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega la columna sucursal_id para filtrar rentas por sucursal
     */
    public function up(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            // Solo agregar si no existe la columna (por seguridad)
            if (!Schema::hasColumn('rentas', 'sucursal_id')) {
                $table->foreignId('sucursal_id')
                    ->nullable()
                    ->after('cliente_id')
                    ->constrained('sucursales')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            if (Schema::hasColumn('rentas', 'sucursal_id')) {
                $table->dropForeign(['sucursal_id']);
                $table->dropColumn('sucursal_id');
            }
        });
    }
};