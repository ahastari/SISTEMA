<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleRenta extends Model
{
    protected $table = 'detalles_rentas';

    protected $fillable = [
        'renta_id',
        'equipo_id',
        'cantidad',
        'cantidad_devuelta',
        'precio_dia',
        'dias',
        'subtotal'
    ];

    public function renta()
    {
        return $this->belongsTo(Renta::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}