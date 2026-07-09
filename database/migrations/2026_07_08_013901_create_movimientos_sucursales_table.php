<?php
// database/migrations/2026_07_08_013901_create_movimientos_sucursales_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movimientos_sucursales', function (Blueprint $table) {
            $table->id();
            
            // Producto
            $table->foreignId('equipo_id')
                  ->constrained('equipos')
                  ->onDelete('cascade');
            
            // Sucursales
            $table->foreignId('sucursal_origen_id')
                  ->constrained('sucursales')
                  ->onDelete('cascade');
            $table->foreignId('sucursal_destino_id')
                  ->constrained('sucursales')
                  ->onDelete('cascade');
            
            // Usuario que realiza el movimiento
            $table->foreignId('usuario_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Cantidad
            $table->integer('cantidad');
            
            // Tipo de movimiento
            $table->enum('tipo', ['entrada', 'salida', 'transferencia', 'ajuste']);
            
            // Estado
            $table->enum('estado', ['pendiente', 'completado', 'cancelado'])->default('completado');
            
            // Motivo y descripción
            $table->string('motivo')->nullable();
            $table->text('descripcion')->nullable();
            
            // Fechas
            $table->timestamp('fecha_movimiento')->useCurrent();
            $table->timestamp('fecha_confirmacion')->nullable();
            
            // Usuario que confirma
            $table->foreignId('confirmado_por')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            $table->timestamps();
            
            // ✅ ÍNDICES CORREGIDOS - Nombres más cortos
            $table->index(['sucursal_origen_id', 'sucursal_destino_id'], 'mov_origen_destino_idx');
            $table->index(['equipo_id', 'fecha_movimiento'], 'mov_equipo_fecha_idx');
            $table->index('tipo', 'mov_tipo_idx');
            $table->index('estado', 'mov_estado_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimientos_sucursales');
    }
};