<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalle_ventas'; 

    // Permite el registro masivo de los campos desde el PuntoVentaController
    protected $fillable = [
        'venta_id',
        'equipo_id',
        'concepto_especial',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    // Relación con la venta
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    // Relación con el equipo/producto
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}