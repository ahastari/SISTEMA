<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SolicitudDescuento;

return new class extends Migration
{
    public function up(): void
    {
        // Descuento aplicado a la renta (subtotal queda BRUTO; total = subtotal - descuento + iva)
        Schema::table('rentas', function (Blueprint $table) {
            $table->decimal('descuento', 12, 2)->default(0);
            $table->string('motivo_descuento', 255)->nullable();
            $table->unsignedBigInteger('descuento_autorizado_por_id')->nullable();
        });

        // Las solicitudes de descuento pueden venir de una renta (origen = 'renta') y quedar ligadas a ella
        Schema::table((new SolicitudDescuento)->getTable(), function (Blueprint $table) {
            $table->string('origen', 20)->nullable();
            $table->unsignedBigInteger('renta_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropColumn(['descuento', 'motivo_descuento', 'descuento_autorizado_por_id']);
        });
        Schema::table((new SolicitudDescuento)->getTable(), function (Blueprint $table) {
            $table->dropColumn(['origen', 'renta_id']);
        });
    }
};