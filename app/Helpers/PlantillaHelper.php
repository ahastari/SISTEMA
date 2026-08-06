<?php

namespace App\Helpers;

use App\Models\PlantillaDocumento;
use Carbon\Carbon;

class PlantillaHelper
{
    public static function renderizarPlantilla($tipo, $renta)
    {
        // 1. Obtener la plantilla guardada por el usuario en configuración
        $plantilla = PlantillaDocumento::where('tipo', $tipo)->first();
        
        if (!$plantilla) {
            return [
                'titulo' => '',
                'contenido' => ''
            ];
        }

        $texto = $plantilla->contenido;

        // Soporte para variaciones de nombres de atributos en el objeto Renta/Venta
        $deposito = $renta->deposito_garantia ?? ($renta->deposito ?? ($renta->monto_deposito ?? 0));
        $montoTotal = $renta->total ?? ($renta->monto_total ?? 0);
        $clienteNombre = $renta->cliente_nombre ?? ($renta->cliente->nombre_completo ?? 'PÚBLICO GENERAL');
        $clienteDireccion = $renta->cliente->direccion ?? ($renta->direccion_cliente ?? 'Dirección no especificada');
        $clienteColonia = $renta->cliente->colonia ?? ($renta->colonia_cliente ?? '');
        $clienteCiudad = $renta->cliente->ciudad ?? ($renta->ciudad_cliente ?? 'Durango, Dgo.');
        $clienteTelefono = $renta->cliente->telefono ?? ($renta->telefono_cliente ?? 'S/T');

        // 2. Mapear todos los datos requeridos por el Contrato y Pagaré de Viramontes
        $reemplazos = [
            '{empresa}'          => ContentHelper::getCompanyData('empresa_nombre') ?? 'ANDAMIOS Y MADERA VIRAMONTES',
            '{dueno_empresa}'    => ContentHelper::getCompanyData('empresa_dueno') ?? 'REPRESENTANTE LEGAL',
            '{ciudad_empresa}'   => 'Durango, Dgo.',
            '{cliente}'          => $clienteNombre,
            '{cliente_nombre}'   => $clienteNombre,
            '{cliente_direccion}'=> $clienteDireccion,
            '{cliente_colonia}'  => $clienteColonia,
            '{cliente_ciudad}'   => $clienteCiudad,
            '{cliente_telefono}' => $clienteTelefono,
            '{folio}'            => $renta->folio ?? ($renta->id ?? 'N/A'),
            '{deposito}'         => number_format((float)$deposito, 2),
            '{deposito_letra}'   => self::numeroALetras((float)$deposito),
            '{monto_total}'      => number_format((float)$montoTotal, 2),
            '{monto_total_letra}'=> self::numeroALetras((float)$montoTotal),
            '{fecha_inicio}'     => isset($renta->created_at) ? Carbon::parse($renta->created_at)->format('d/m/Y') : date('d/m/Y'),
            '{fecha_fin}'        => isset($renta->fecha_devolucion_estimada) ? Carbon::parse($renta->fecha_devolucion_estimada)->format('d/m/Y') : (isset($renta->fecha_fin) ? Carbon::parse($renta->fecha_fin)->format('d/m/Y') : date('d/m/Y')),
            '{obra_nombre}'      => $renta->obra_nombre ?? 'Obra Particular',
            '{obra_direccion}'   => $renta->obra_direccion ?? 'Misma dirección',
        ];

        // 3. Reemplazar dinámicamente las etiquetas en la plantilla
        foreach ($reemplazos as $tag => $valor) {
            $texto = str_replace($tag, $valor, $texto);
        }

        return [
            'titulo'    => $plantilla->titulo,
            'contenido' => $texto
        ];
    }

    private static function numeroALetras($numero)
    {
        if ($numero <= 0) {
            return "CERO PESOS 00/100 M.N.";
        }

        try {
            $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
            $entero = floor($numero);
            $decimales = round(($numero - $entero) * 100);
            $texto = strtoupper($formatter->format($entero));
            return "{$texto} PESOS " . str_pad($decimales, 2, '0', STR_PAD_LEFT) . "/100 M.N.";
        } catch (\Exception $e) {
            return number_format($numero, 2) . " PESOS M.N.";
        }
    }
}