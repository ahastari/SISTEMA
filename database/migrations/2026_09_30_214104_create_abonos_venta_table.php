<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonos_venta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();          // quien cobró el abono
            $table->unsignedBigInteger('sucursal_id')->nullable()->index();
            $table->unsignedBigInteger('corte_caja_id')->nullable();    // caja donde entró el dinero
            $table->decimal('monto', 12, 2);
            $table->string('metodo', 20);                               // efectivo | transferencia | tarjeta
            $table->string('referencia', 100)->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonos_venta');
    }
};