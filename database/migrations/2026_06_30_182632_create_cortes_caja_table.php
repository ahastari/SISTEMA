<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('turno')->default('mañana');
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('monto_final', 10, 2)->nullable();
            $table->decimal('total_ventas', 10, 2)->default(0);
            $table->decimal('total_efectivo', 10, 2)->default(0);
            $table->decimal('total_transferencias', 10, 2)->default(0);
            $table->decimal('total_tarjetas', 10, 2)->default(0);
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->string('estado')->default('abierto'); // abierto, cerrado
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_caja');
    }
};