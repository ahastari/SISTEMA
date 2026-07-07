<?php

namespace App\Helpers;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Cache;

class ContentHelper
{
    /**
     * Obtiene una configuración por su llave, usando Cache para máxima velocidad del sistema.
     */
    public static function getCompanyData(string $key, string $default = ''): string
    {
        // Almacenamos en caché por 24 horas para no saturar la base de datos en cada carga de página
        return Cache::remember("config_{$key}", 86400, function () use ($key, $default) {
            $config = Configuracion::where('key', $key)->first();
            return $config && !empty($config->value) ? $config->value : $default;
        });
    }
    public static function parseTemplate(string $content, $renta): string
    {
        $variables = [
            '{cliente}'         => $renta->cliente->nombre_completo,
            '{folio}'           => $renta->folio,
            '{monto_total}'     => '$' . number_format($renta->total, 2),
            '{monto_neto}'      => '$' . number_format($renta->total - ($renta->deposito ?? 0), 2),
            '{deposito}'        => '$' . number_format($renta->deposito ?? 0, 2),
            '{fecha_fin}'       => \Carbon\Carbon::parse($renta->fecha_fin)->format('d/m/Y'),
            '{fecha_actual}'    => \Carbon\Carbon::now()->format('d/m/Y'),
            '{empresa}'         => self::getCompanyData('empresa_nombre', 'la empresa'),
        ];

        return str_replace(array_keys($variables), array_values($variables), $content);
    }
}