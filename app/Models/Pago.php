<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'renta_id', 'monto', 'metodo_pago', 'referencia', 'fecha_pago', 'tipo', 'observaciones'
    ];

    protected $casts = [
        'fecha_pago' => 'date'
    ];

    public function renta()
    {
        return $this->belongsTo(Renta::class);
    }
}