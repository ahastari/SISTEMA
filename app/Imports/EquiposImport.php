<?php

namespace App\Imports;

use App\Models\Equipo;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class EquiposImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // 1. Detección Inteligente de Columnas
            $nombre = $this->findValue($row, ['nombre', 'producto', 'equipo', 'articulo', 'descripcion_corta', 'name']);
            if (empty($nombre)) continue; 

            $codigo = $this->findValue($row, ['codigo', 'sku', 'clave', 'referencia', 'id']);
            $codigoBarras = $this->findValue($row, ['codigo_barras', 'barcode', 'upc', 'ean']);
            $categoriaName = $this->findValue($row, ['categoria', 'familia', 'grupo', 'linea', 'tipo_producto']);
            $unidadName = $this->findValue($row, ['unidad', 'medida', 'uom', 'presentacion']);
            $tipoOperacion = $this->findValue($row, ['operacion', 'tipo', 'renta_venta', 'disponible']);
            $precioDia = $this->findValue($row, ['precio_dia', 'renta', 'tarifa', 'precio_renta']);
            $precioVenta = $this->findValue($row, ['precio_venta', 'venta', 'costo', 'precio']);
            $stock = $this->findValue($row, ['stock', 'cantidad', 'inventario', 'existencia', 'qty']);
            $stockMinimo = $this->findValue($row, ['minimo', 'alerta', 'reorden']);
            $descripcion = $this->findValue($row, ['descripcion', 'detalles', 'notas', 'comentarios']);

            // 2. Limpieza de datos
            $precioDia = $this->cleanNumber($precioDia);
            $precioVenta = $this->cleanNumber($precioVenta);
            $stock = intval($this->cleanNumber($stock));
            $stockMinimo = intval($this->cleanNumber($stockMinimo));

            $tipoOperacion = strtolower(trim($tipoOperacion));
            if (!in_array($tipoOperacion, ['renta', 'venta', 'ambas'])) {
                $tipoOperacion = str_contains($tipoOperacion, 'renta') ? 'renta' : 
                                (str_contains($tipoOperacion, 'venta') ? 'venta' : 'ambas');
            }

            // 3. Buscar o crear relaciones
            $categoria = Categoria::firstOrCreate(
                ['nombre' => $categoriaName ?: 'General'],
                ['activa' => true]
            );

            $unidad = UnidadMedida::firstOrCreate(
                ['abreviatura' => $unidadName ? substr($unidadName, 0, 5) : 'PZA'],
                ['nombre' => $unidadName ?: 'Pieza', 'activa' => true]
            );

            // 4. Generar código automático si no existe
            if (empty($codigo)) {
                $codigo = $this->generarCodigo($categoria->id);
            }

            // 5. Guardar equipo
            Equipo::updateOrCreate(
                ['codigo' => $codigo],
                [
                    'codigo_barras' => $codigoBarras ?: null,
                    'nombre' => $nombre,
                    'categoria_id' => $categoria->id,
                    'unidad_medida_id' => $unidad->id,
                    'tipo_operacion' => $tipoOperacion,
                    'precio_dia' => $precioDia ?: 0,
                    'precio_venta' => $precioVenta ?: 0,
                    'stock' => $stock ?: 0,
                    'stock_minimo' => $stockMinimo ?: 0,
                    'descripcion' => $descripcion ?: '',
                    'activo' => true,
                ]
            );
        }
    }

    private function findValue($row, $aliases)
    {
        foreach ($row as $key => $value) {
            $normalizedKey = Str::slug($key, '_');
            foreach ($aliases as $alias) {
                if (str_contains($normalizedKey, $alias)) {
                    return $value;
                }
            }
        }
        return null;
    }

    private function cleanNumber($val)
    {
        if (empty($val)) return 0;
        $cleaned = preg_replace('/[^0-9\.]/', '', $val);
        return floatval($cleaned);
    }

    private function generarCodigo($categoriaId)
    {
        $categoria = Categoria::find($categoriaId);
        $prefijos = [
            'Andamios' => 'AND', 'Ruedas' => 'RUE', 'Flete' => 'FLE',
            'Madera' => 'MAD', 'Herramientas' => 'HER', 
            'Equipo de Seguridad' => 'SEG', 'Maquinaria' => 'MAQ'
        ];
        
        $prefijo = $prefijos[$categoria->nombre ?? ''] ?? 'GEN';
        $ultimoEquipo = Equipo::where('codigo', 'like', $prefijo . '-%')->orderBy('codigo', 'desc')->first();
        
        $nuevoNumero = $ultimoEquipo ? intval(substr($ultimoEquipo->codigo, strlen($prefijo) + 1)) + 1 : 1;
        return $prefijo . '-' . str_pad($nuevoNumero, 3, '0', STR_PAD_LEFT);
    }
}