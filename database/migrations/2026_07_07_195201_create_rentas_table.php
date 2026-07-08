<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rentas', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('obra_id')->nullable()->constrained('obras')->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('dias_totales');
            $table->integer('dias_ampliados')->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('iva', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('total_pagado', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);
            $table->decimal('deposito', 10, 2)->nullable()->default(0);
            $table->string('estado')->default('activa');
            $table->text('observaciones')->nullable();
            $table->string('contrato_firmado_path')->nullable();
            $table->string('pagare_firmado_path')->nullable();
            $table->date('fecha_devolucion')->nullable();
            $table->date('fecha_ampliacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('rentas');
    }
};