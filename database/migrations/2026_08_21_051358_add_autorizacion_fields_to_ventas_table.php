<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('autorizacion_solicitada')->default(false)->after('estado');
            $table->unsignedBigInteger('solicitado_por_id')->nullable()->after('autorizacion_solicitada');
            $table->unsignedBigInteger('autorizado_por_id')->nullable()->after('solicitado_por_id');
            $table->string('motivo_cancelacion')->nullable()->after('autorizado_por_id');
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['autorizacion_solicitada', 'solicitado_por_id', 'autorizado_por_id', 'motivo_cancelacion']);
        });
    }
};
