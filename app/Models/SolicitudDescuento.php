<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudDescuento extends Model
{
    protected $table = 'solicitudes_descuento';

    protected $fillable = [
        'user_id', 'sucursal_id', 'corte_caja_id', 'monto', 'subtotal', 'motivo',
        'estado', 'autorizado_por_id', 'resuelta_at', 'venta_id',
    ];

    protected $casts = [
        'monto'       => 'decimal:2',
        'subtotal'    => 'decimal:2',
        'resuelta_at' => 'datetime',
    ];

    // Cajero que solicita
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Gerente/Admin que resolvió
    public function autorizador()
    {
        return $this->belongsTo(User::class, 'autorizado_por_id');
    }
}