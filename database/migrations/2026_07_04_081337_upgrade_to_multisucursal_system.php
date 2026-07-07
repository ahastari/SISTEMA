<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de Sucursales - solo crear si no existe
        if (!Schema::hasTable('sucursales')) {
            Schema::create('sucursales', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('direccion')->nullable();
                $table->string('telefono')->nullable();
                $table->string('rfc')->nullable();
                $table->string('logo')->nullable();
                $table->boolean('activa')->default(true);
                $table->timestamps();
            });
        }

        // Tabla de Plantillas de Documentos - solo crear si no existe
        if (!Schema::hasTable('plantillas_documentos')) {
            Schema::create('plantillas_documentos', function (Blueprint $table) {
                $table->id();
                $table->string('tipo')->unique();
                $table->string('titulo');
                $table->longText('contenido');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_documentos');
        Schema::dropIfExists('sucursales');
    }
};