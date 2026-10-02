<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('tipo_tarifa', 10)->default('dia')->after('precio_dia');
        });

        // Marca como 'm2' los productos existentes cuya unidad de medida ya sea metro cuadrado
        $ids = DB::table('unidades_medida')
            ->where(function ($q) {
                $q->whereIn(DB::raw("LOWER(REPLACE(REPLACE(abreviatura, '²', '2'), ' ', ''))"), ['m2', 'mt2', 'mts2'])
                  ->orWhere('nombre', 'like', '%metro cuadrado%')
                  ->orWhere('nombre', 'like', '%metros cuadrados%');
            })
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('equipos')->whereIn('unidad_medida_id', $ids)->update(['tipo_tarifa' => 'm2']);
        }
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('tipo_tarifa');
        });
    }
};