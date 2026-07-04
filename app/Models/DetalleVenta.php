<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalles_venta'; // O el nombre que tenga tu tabla de detalles

    // Agrega este bloque para permitir el registro masivo de los campos
    protected $fillable = [
        'venta_id',
        'equipo_id',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    // Relación con la venta (Opcional, por si no la tenías agregada)
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    // Relación con el equipo/producto
    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}