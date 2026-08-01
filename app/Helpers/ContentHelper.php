<?php

namespace App\Helpers;

use App\Models\Configuracion;
use App\Models\Sucursal;
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

    /**
     * Obtener los datos de la sucursal activa del usuario como array
     * CORREGIDO: Almacenamos arrays en caché, no objetos Eloquent
     */
    public static function getSucursalActiva(): array
    {
        $sucursalId = session('activo_sucursal_id');
        
        // Si es admin global o no hay sucursal asignada
        if ($sucursalId === 'global' || !$sucursalId) {
            return [
                'id' => 'global',
                'nombre' => 'Consola Global Corporativa',
                'direccion' => 'Todas las sucursales',
                'telefono' => self::getCompanyData('empresa_telefono', ''),
                'logo' => self::getCompanyData('empresa_logo', ''),
            ];
        }

        // 🔧 CORRECCIÓN: Almacenar array en caché, NO el objeto Eloquent
        $sucursalData = Cache::remember("sucursal_data_{$sucursalId}", 3600, function () use ($sucursalId) {
            $sucursal = Sucursal::find($sucursalId);
            
            if (!$sucursal) {
                return null;
            }
            
            // Devolver solo los datos necesarios como array
            return [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'direccion' => $sucursal->direccion ?? '',
                'telefono' => $sucursal->telefono ?? '',
                'logo' => $sucursal->logo ?? '',
                'activa' => $sucursal->activa,
            ];
        });

        // Si no se encontró la sucursal en BD o en caché
        if (!$sucursalData) {
            return [
                'id' => $sucursalId,
                'nombre' => 'Sucursal no encontrada',
                'direccion' => '',
                'telefono' => '',
                'logo' => '',
            ];
        }

        return $sucursalData;
    }

   /**
     * Obtener el logo a mostrar según contexto (sucursal o empresa)
     */
    public static function getLogoActual(): string
    {
        $sucursalData = self::getSucursalActiva();
        $logo = '';

        // 1. Intentar obtener el logo de la sucursal activa
        if ($sucursalData['id'] !== 'global' && !empty($sucursalData['logo'])) {
            $pathPrueba = str_replace(['public/', 'storage/'], '', $sucursalData['logo']);
            // Solo usar si el archivo físico realmente existe en storage/app/public
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($pathPrueba)) {
                $logo = $sucursalData['logo'];
            }
        } 

        // 2. Si no hay logo de sucursal o el archivo no existe, usar el logo corporativo
        if (empty($logo)) {
            $logo = self::getCompanyData('empresa_logo', '');
        }

        // 3. Fallback final: Buscar cualquier sucursal con un archivo de logo válido en disco
        if (empty($logo)) {
            $sucursales = \App\Models\Sucursal::whereNotNull('logo')->where('logo', '!=', '')->get();
            foreach ($sucursales as $s) {
                $p = str_replace(['public/', 'storage/'], '', $s->logo);
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($p)) {
                    $logo = $s->logo;
                    break;
                }
            }
        }

        if (empty($logo)) {
            return '';
        }

        // Sanitizar y devolver la URL web
        $cleanPath = str_replace(['public/', 'storage/'], '', $logo);
        $cleanPath = ltrim($cleanPath, '/\\');

        return asset('storage/' . $cleanPath);
    }

    /**
     * Obtener el nombre a mostrar en el sidebar/navbar
     */
    public static function getNombreMostrar(): string
    {
        $sucursalData = self::getSucursalActiva();
        
        if ($sucursalData['id'] !== 'global') {
            return $sucursalData['nombre'];
        }
        
        return self::getCompanyData('empresa_nombre', 'Sistema de Gestión');
    }

    /**
     * Verificar si el usuario actual está en modo sucursal específica
     */
    public static function isSucursalEspecifica(): bool
    {
        $sucursalId = session('activo_sucursal_id');
        return $sucursalId && $sucursalId !== 'global';
    }

    /**
     * Parsear plantilla de documento con variables dinámicas
     */
    public static function parseTemplate(string $content, $renta): string
    {
        $variables = [
            '{cliente}'         => $renta->cliente->nombre_completo ?? 'Cliente',
            '{folio}'           => $renta->folio ?? 'N/A',
            '{monto_total}'     => '$' . number_format($renta->total ?? 0, 2),
            '{monto_neto}'      => '$' . number_format(($renta->total ?? 0) - ($renta->deposito ?? 0), 2),
            '{deposito}'        => '$' . number_format($renta->deposito ?? 0, 2),
            '{fecha_fin}'       => $renta->fecha_fin ? \Carbon\Carbon::parse($renta->fecha_fin)->format('d/m/Y') : 'N/A',
            '{fecha_actual}'    => \Carbon\Carbon::now()->format('d/m/Y'),
            '{empresa}'         => self::getCompanyData('empresa_nombre', 'la empresa'),
        ];

        return str_replace(array_keys($variables), array_values($variables), $content);
    }
}