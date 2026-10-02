<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_descuento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('corte_caja_id')->nullable();
            $table->decimal('monto', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->string('motivo');
            // pendiente | aprobada | rechazada | usada | cancelada
            $table->string('estado', 20)->default('pendiente')->index();
            $table->foreignId('autorizado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resuelta_at')->nullable();
            $table->unsignedBigInteger('venta_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_descuento');
    }
};