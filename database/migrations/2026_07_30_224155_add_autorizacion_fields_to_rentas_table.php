<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->boolean('autorizacion_solicitada')->default(false)->after('estado');
            $table->string('motivo_autorizacion')->nullable()->after('autorizacion_solicitada');
            $table->json('datos_pendientes_finalizacion')->nullable()->after('motivo_autorizacion');
        });
    }

    public function down()
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropColumn(['autorizacion_solicitada', 'motivo_autorizacion', 'datos_pendientes_finalizacion']);
        });
    }
};