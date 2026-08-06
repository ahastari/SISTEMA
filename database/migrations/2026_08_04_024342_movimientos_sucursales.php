<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimientos_sucursales', function (Blueprint $table) {
            // Si la columna ya existe como string, asegúrate de actualizar la documentación de valores válidos.
            // Si era un ENUM, cambia/modifica la columna:
            $table->string('estado')->default('pendiente')->change();
        });
    }

    public function down(): void
    {
        //
    }
};
