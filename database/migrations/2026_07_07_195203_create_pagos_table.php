<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renta_id')->constrained('rentas')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago'); // efectivo, transferencia, tarjeta
            $table->string('referencia')->nullable(); // número de transferencia, etc.
            $table->date('fecha_pago');
            $table->string('tipo'); // finalizacion, abono, parcial
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};