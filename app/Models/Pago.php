<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'renta_id', 
        'monto', 
        'metodo_pago', 
        'referencia', 
        'desglose_mixto', 
        'fecha_pago', 
        'tipo', 
        'observaciones'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'desglose_mixto' => 'array',
    ];

    public function renta()
    {
        return $this->belongsTo(Renta::class);
    }
}