<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('detalles_rentas', function (Blueprint $table) {
            $table->integer('cantidad_devuelta')->default(0)->after('cantidad');
        });
    }

    public function down()
    {
        Schema::table('detalles_rentas', function (Blueprint $table) {
            $table->dropColumn('cantidad_devuelta');
        });
    }
};
