<?php
// database/migrations/2026_07_08_013819_create_equipo_sucursal_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ✅ Verificar que la tabla no exista antes de crearla
        if (!Schema::hasTable('equipo_sucursal')) {
            Schema::create('equipo_sucursal', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipo_id')
                      ->constrained('equipos')
                      ->onDelete('cascade');
                $table->foreignId('sucursal_id')
                      ->constrained('sucursales')
                      ->onDelete('cascade');
                $table->integer('stock')->default(0);
                $table->integer('stock_minimo')->default(0);
                $table->timestamps();
                
                $table->unique(['equipo_id', 'sucursal_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('equipo_sucursal');
    }
};