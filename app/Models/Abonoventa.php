<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonoVenta extends Model
{
    protected $table = 'abonos_venta';

    protected $fillable = [
        'venta_id', 'user_id', 'sucursal_id', 'corte_caja_id',
        'monto', 'metodo', 'referencia', 'observaciones',
    ];

    protected $casts = ['monto' => 'float'];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}