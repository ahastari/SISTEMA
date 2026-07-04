<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corte_caja_id')->constrained('cortes_caja')->onDelete('cascade');
            $table->string('tipo'); // ingreso, egreso
            $table->string('concepto');
            $table->decimal('monto', 10, 2);
            $table->string('metodo')->default('efectivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};