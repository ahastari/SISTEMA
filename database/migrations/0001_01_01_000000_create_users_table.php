<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // 🛠️ Columnas Corporativas y de Control Multisucursal Integradas
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->constrained('sucursales')
                  ->onDelete('set null'); // Si se borra una sucursal, el usuario no se elimina, queda sin asignar.
                  
            $table->string('role')->default('cajero'); // Sincronizado en inglés ('admin', 'gerente', 'cajero')
            $table->string('status')->default('activo'); // Control de acceso ('activo', 'baja')
            $table->string('foto')->nullable(); // Ruta de la foto de perfil

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};