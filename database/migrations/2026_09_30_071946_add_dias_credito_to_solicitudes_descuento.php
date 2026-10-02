<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SolicitudDescuento;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table((new SolicitudDescuento)->getTable(), function (Blueprint $table) {
            $table->unsignedSmallInteger('dias_credito')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table((new SolicitudDescuento)->getTable(), function (Blueprint $table) {
            $table->dropColumn('dias_credito');
        });
    }
};