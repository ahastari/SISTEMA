<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalles_rentas', function (Blueprint $table) {
            $table->decimal('precio_venta', 12, 2)->nullable()->after('precio_dia');
        });

        // Rentas existentes: se congela el precio actual del catálogo
        DB::statement('
            UPDATE detalles_rentas dr
            JOIN equipos e ON e.id = dr.equipo_id
            SET dr.precio_venta = e.precio_venta
            WHERE dr.precio_venta IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('detalles_rentas', fn (Blueprint $t) => $t->dropColumn('precio_venta'));
    }
};
