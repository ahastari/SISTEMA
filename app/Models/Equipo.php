<?php
// app/Models/Equipo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';

    protected $fillable = [
        'codigo', 'codigo_barras', 'nombre', 'categoria_id', 'unidad_medida_id', 'tipo_operacion',
        'precio_dia', 'precio_venta', 'stock', 'stock_minimo', 'imagen', 'descripcion', 'activo'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    // Relación con sucursales a través de la tabla pivote
    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'equipo_sucursal')
                    ->withPivot('stock', 'stock_minimo')
                    ->withTimestamps();
    }

    // Obtener stock en una sucursal específica
    public function getStockEnSucursal($sucursalId)
    {
        $pivot = $this->sucursales()->where('sucursal_id', $sucursalId)->first();
        return $pivot ? $pivot->pivot->stock : 0;
    }

    // Actualizar stock en una sucursal
    public function actualizarStockEnSucursal($sucursalId, $cantidad, $operacion = 'sumar')
    {
        $pivot = $this->sucursales()->where('sucursal_id', $sucursalId)->first();
        
        if (!$pivot) {
            // Si no existe, crear registro
            $this->sucursales()->attach($sucursalId, [
                'stock' => max(0, $cantidad),
                'stock_minimo' => $this->stock_minimo ?? 0
            ]);
            return;
        }

        $nuevoStock = $operacion === 'sumar' 
            ? $pivot->pivot->stock + $cantidad 
            : $pivot->pivot->stock - $cantidad;
        
        $this->sucursales()->updateExistingPivot($sucursalId, [
            'stock' => max(0, $nuevoStock)
        ]);
    }

    // Verificar disponibilidad en una sucursal
    public function disponibleEnSucursal($sucursalId, $cantidad)
    {
        return $this->getStockEnSucursal($sucursalId) >= $cantidad;
    }
}