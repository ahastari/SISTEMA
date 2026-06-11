<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('telefono');
            $table->string('email')->nullable();
            $table->string('rfc')->nullable();
            $table->string('curp')->nullable();
            
            // INE - Archivo y número
            $table->string('ine_numero')->nullable();
            $table->string('ine_documento')->nullable(); // Ruta del archivo
            
            // Documentos adicionales
            $table->string('contrato_firmado')->nullable(); // Ruta del contrato
            $table->string('comprobante_deposito')->nullable(); // Ruta del comprobante
            
            $table->string('telefono_alternativo')->nullable();
            $table->string('empresa')->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estado')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};