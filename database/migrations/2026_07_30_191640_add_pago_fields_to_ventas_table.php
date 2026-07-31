<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Solo agrega si no existe la columna en la BD
            if (!Schema::hasColumn('ventas', 'monto_recibido')) {
                $table->decimal('monto_recibido', 10, 2)->nullable()->after('total');
            }

            if (!Schema::hasColumn('ventas', 'cambio')) {
                $table->decimal('cambio', 10, 2)->nullable()->after('monto_recibido');
            }

            if (!Schema::hasColumn('ventas', 'pagos_mixtos')) {
                $table->json('pagos_mixtos')->nullable()->after('cambio');
            }
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Revertir eliminando solo si existen
            $columnsToDrop = [];

            if (Schema::hasColumn('ventas', 'monto_recibido')) {
                $columnsToDrop[] = 'monto_recibido';
            }
            if (Schema::hasColumn('ventas', 'cambio')) {
                $columnsToDrop[] = 'cambio';
            }
            if (Schema::hasColumn('ventas', 'pagos_mixtos')) {
                $columnsToDrop[] = 'pagos_mixtos';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};