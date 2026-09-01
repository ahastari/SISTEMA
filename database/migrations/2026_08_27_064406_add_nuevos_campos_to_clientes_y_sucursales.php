<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Campo para comprobante de domicilio
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('comprobante_domicilio_path')->nullable()->after('rfc');
        });

        // Campo para controlar el folio manualmente por sucursal
        Schema::table('sucursales', function (Blueprint $table) {
            $table->integer('siguiente_folio_rentas')->nullable()->after('activa');
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('comprobante_domicilio_path');
        });
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn('siguiente_folio_rentas');
        });
    }
};