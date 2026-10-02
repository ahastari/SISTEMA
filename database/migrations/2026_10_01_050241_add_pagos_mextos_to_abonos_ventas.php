<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonos_venta', function (Blueprint $table) {
            // Desglose de un abono mixto: [{"metodo":"efectivo","monto":500},{"metodo":"tarjeta","monto":300}]
            $table->json('pagos_mixtos')->nullable()->after('metodo');
        });
    }

    public function down(): void
    {
        Schema::table('abonos_venta', function (Blueprint $table) {
            $table->dropColumn('pagos_mixtos');
        });
    }
};