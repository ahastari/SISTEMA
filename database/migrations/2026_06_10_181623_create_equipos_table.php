<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Ej: AND-001
            $table->string('nombre'); // Ej: "Andamio Tablón"
            $table->string('categoria'); // Ej: "Andamios", "Ruedas", "Flete"
            $table->decimal('precio_dia', 10, 2);
            $table->decimal('precio_semana', 10, 2)->nullable();
            $table->decimal('precio_mes', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('imagen')->nullable(); // Ruta de imagen del equipo
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};