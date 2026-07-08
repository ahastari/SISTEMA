<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_rentas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renta_id')->constrained('rentas')->onDelete('cascade');
            $table->foreignId('equipo_id')->constrained('equipos');
            $table->integer('cantidad');
            $table->decimal('precio_dia', 10, 2);
            $table->integer('dias');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_rentas');
    }
};