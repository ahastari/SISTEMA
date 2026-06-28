<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    protected $fillable = [
        'renta_id', 'monto', 'metodo_pago', 'referencia', 'fecha_abono', 'observaciones'
    ];

    protected $casts = [
        'fecha_abono' => 'date'
    ];

    public function renta()
    {
        return $this->belongsTo(Renta::class);
    }
}