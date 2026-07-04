<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    // Agrega esta línea. Si tu tabla se llama de otra forma (ej. 'movimientos_caja'), cámbialo aquí:
    protected $table = 'movimientos_caja'; 

    protected $fillable = [
        'corte_caja_id', 'tipo', 'concepto', 'monto', 'metodo'
    ];

    public function corteCaja()
    {
        return $this->belongsTo(CorteCaja::class);
    }
}