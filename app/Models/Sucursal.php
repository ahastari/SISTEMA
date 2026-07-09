<?php
// app/Models/Sucursal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';
    protected $fillable = ['nombre', 'direccion', 'telefono', 'rfc', 'logo', 'activa'];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }

    // Relación con equipos a través de la tabla pivote
    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'equipo_sucursal')
                    ->withPivot('stock', 'stock_minimo')
                    ->withTimestamps();
    }

    // Movimientos de origen
    public function movimientosOrigen()
    {
        return $this->hasMany(MovimientoSucursal::class, 'sucursal_origen_id');
    }

    // Movimientos de destino
    public function movimientosDestino()
    {
        return $this->hasMany(MovimientoSucursal::class, 'sucursal_destino_id');
    }
}