<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimientos_sucursales', function (Blueprint $table) {
            // Cambiar la columna 'estado' para que sea string con default 'pendiente'
            $table->string('estado')->default('pendiente')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_sucursales', function (Blueprint $table) {
            // Revertir al estado anterior (ejemplo: sin default)
            $table->string('estado')->nullable()->change();
        });
    }
};
