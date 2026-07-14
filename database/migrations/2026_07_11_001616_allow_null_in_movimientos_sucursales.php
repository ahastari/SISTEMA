<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alteramos la tabla para permitir valores nulos (NULL)
        DB::statement('ALTER TABLE movimientos_sucursales MODIFY sucursal_origen_id BIGINT UNSIGNED NULL;');
        DB::statement('ALTER TABLE movimientos_sucursales MODIFY sucursal_destino_id BIGINT UNSIGNED NULL;');
    }

    public function down(): void
    {
        // Revertir en caso de ser necesario
        DB::statement('ALTER TABLE movimientos_sucursales MODIFY sucursal_origen_id BIGINT UNSIGNED NOT NULL;');
        DB::statement('ALTER TABLE movimientos_sucursales MODIFY sucursal_destino_id BIGINT UNSIGNED NOT NULL;');
    }
};