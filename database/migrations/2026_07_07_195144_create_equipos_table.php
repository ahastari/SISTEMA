<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            
            // Agregamos la columna sucursal_id
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->onDelete('cascade');
            
            $table->string('codigo')->unique();
            $table->string('codigo_barras')->nullable()->unique();
            $table->string('nombre');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->foreignId('unidad_medida_id')->nullable()->constrained('unidades_medida')->onDelete('set null');
            $table->string('tipo_operacion')->default('renta'); 
            $table->decimal('precio_dia', 10, 2)->nullable();
            $table->decimal('precio_venta', 10, 2)->nullable(); 
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(0); 
            $table->string('imagen')->nullable(); 
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('equipos');
    }
};