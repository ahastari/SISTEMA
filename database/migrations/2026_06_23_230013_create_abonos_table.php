<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renta_id')->constrained('rentas')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago'); // efectivo, transferencia, tarjeta
            $table->string('referencia')->nullable();
            $table->date('fecha_abono');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonos');
    }
};