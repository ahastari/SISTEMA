<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentas', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique(); // R-2026-0001
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('dias_totales');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('iva', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('deposito', 10, 2)->nullable()->default(0);
            $table->string('estado')->default('activa'); // activa, finalizada, cancelada
            $table->text('observaciones')->nullable();
            $table->date('fecha_devolucion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentas');
    }
};