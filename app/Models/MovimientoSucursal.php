<?php
// app/Models/MovimientoSucursal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoSucursal extends Model
{
    use HasFactory;

    protected $table = 'movimientos_sucursales';

    protected $fillable = [
        'equipo_id',
        'sucursal_origen_id',
        'sucursal_destino_id',
        'usuario_id',
        'cantidad',
        'tipo',
        'estado',
        'motivo',
        'descripcion',
        'fecha_movimiento',
        'fecha_confirmacion',
        'confirmado_por'
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
        'fecha_confirmacion' => 'datetime',
    ];

    // Relaciones
    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }

    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function confirmadoPor()
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_origen_id', $sucursalId)
                     ->orWhere('sucursal_destino_id', $sucursalId);
    }

    // Métodos de ayuda
    public function esTransferencia()
    {
        return $this->tipo === 'transferencia';
    }

    public function esEntrada()
    {
        return $this->tipo === 'entrada';
    }

    public function esSalida()
    {
        return $this->tipo === 'salida';
    }

    public function completar()
    {
        $this->estado = 'completado';
        $this->fecha_confirmacion = now();
        $this->save();
    }

    public function cancelar()
    {
        $this->estado = 'cancelado';
        $this->save();
    }
}