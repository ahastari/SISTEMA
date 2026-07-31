<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('equipo_id')->nullable()->change();
            if (!Schema::hasColumn('detalle_ventas', 'concepto_especial')) {
                $table->string('concepto_especial')->nullable()->after('equipo_id');
            }
        });
    }

    public function down()
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('equipo_id')->nullable(false)->change();
            $table->dropColumn('concepto_especial');
        });
    }
};
