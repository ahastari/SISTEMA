<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregamos la columna a clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('sucursal_id')->nullable()->after('id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
        });

        // Agregamos la columna a obras
        Schema::table('obras', function (Blueprint $table) {
            $table->unsignedBigInteger('sucursal_id')->nullable()->after('id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });

        Schema::table('obras', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};