<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // En PostgreSQL se usa ALTER COLUMN y no existe UNSIGNED
        DB::statement('ALTER TABLE movimientos_sucursales ALTER COLUMN sucursal_origen_id DROP NOT NULL;');
        DB::statement('ALTER TABLE movimientos_sucursales ALTER COLUMN sucursal_destino_id DROP NOT NULL;');
    }

    public function down(): void
    {
        // Revertir para que vuelvan a ser NOT NULL
        DB::statement('ALTER TABLE movimientos_sucursales ALTER COLUMN sucursal_origen_id SET NOT NULL;');
        DB::statement('ALTER TABLE movimientos_sucursales ALTER COLUMN sucursal_destino_id SET NOT NULL;');
    }
};
