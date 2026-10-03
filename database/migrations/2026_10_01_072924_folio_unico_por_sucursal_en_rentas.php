<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            // El nombre por defecto de Laravel es rentas_folio_unique
            try { $table->dropUnique('rentas_folio_unique'); } catch (\Throwable $e) {}

            // Ahora el folio es único POR sucursal
            $table->unique(['sucursal_id', 'folio'], 'rentas_sucursal_folio_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropUnique('rentas_sucursal_folio_unique');
        });
    }
};