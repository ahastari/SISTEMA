<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rentas', function (Blueprint $table) {
            // boolean para saber si la renta lleva IVA
            $table->boolean('facturar')->default(false);
        });
    }

    public function down()
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropColumn('facturar');
        });
    }
};