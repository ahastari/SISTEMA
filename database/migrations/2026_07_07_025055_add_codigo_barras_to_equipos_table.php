<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Añadimos la columna después del código interno, permitiendo que sea nula o única
            $table->string('codigo_barras')->nullable()->unique()->after('codigo');
        });
    }

    public function down()
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('codigo_barras');
        });
    }
};