<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorteCaja extends Model
{
    protected $table = 'cortes_caja';

    protected $fillable = [
        'user_id', 'turno', 'fecha_apertura', 'fecha_cierre', 'monto_inicial',
        'monto_final', 'total_ventas', 'total_efectivo', 'total_transferencias',
        'total_tarjetas', 'diferencia', 'estado', 'observaciones'
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}