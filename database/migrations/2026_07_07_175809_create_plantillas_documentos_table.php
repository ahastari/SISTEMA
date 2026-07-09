<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Creamos la tabla solo si no existe en la base de datos
        if (!Schema::hasTable('plantillas_documentos')) {
            Schema::create('plantillas_documentos', function (Blueprint $table) {
                $table->id();
                $table->string('tipo')->unique(); // 'contrato', 'pagare'
                $table->string('titulo');
                $table->longText('contenido'); // Soporta las cláusulas largas
                $table->timestamps();
            });
        }

        // 2. Inyectamos de una vez los formatos base para el Contrato y el Pagaré
        // de forma que el sistema no inicie en blanco y aparezcan de inmediato.
        DB::table('plantillas_documentos')->updateOrInsert(
            ['tipo' => 'contrato'],
            [
                'titulo' => 'CONTRATO DE PRESTACIÓN DE SERVICIOS DE RENTA',
                'contenido' => "1. - El prestador de servicios se compromete a entregar en perfectas condiciones de trabajo el equipo al cliente.\n2. - El cliente {cliente} tiene la obligación de verificar el buen estado en que recibe el equipo y entregarlo de igual forma.\n3. - Las piezas faltantes o averiadas se cobrarán en efectivo.\n4. - En la renta del equipo NO HAY CRÉDITO por lo que al devolver el equipo se deberá liquidar la renta.\n5. - El cliente está obligado a dejar un depósito por la cantidad de {deposito} que garantiza la devolución del equipo en buen estado.\n6. - El prestador de servicios {empresa} se compromete a no hacer uso de este depósito, salvo si el cliente llegara a hacer mal uso del equipo.",
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('plantillas_documentos')->updateOrInsert(
            ['tipo' => 'pagare'],
            [
                'titulo' => 'PAGARÉ',
                'contenido' => "DEBO (EMOS) Y PAGARÉ (EMOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE {empresa} EN DURANGO, DGO. EL DÍA {fecha_fin} LA CANTIDAD DE {monto_neto} VALOR RECIBIDO A MI (NUESTRA) ENTERA SATISFACCIÓN, EN CASO DE DEMORA PARCIALMENTE INSOLUTO SIN QUE POR ELLO SE CONSIDERE PRORROGADO EL PLAZO FIJADO.",
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_documentos');
    }
};