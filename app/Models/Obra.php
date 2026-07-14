<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    protected $fillable = [
        'nombre', 'direccion', 'colonia', 'ciudad', 'estado', 
        'codigo_postal', 'telefono_obra', 'contacto_obra', 
        'cliente_id', 'observaciones', 'activa',
        'sucursal_id'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function rentas()
    {
        return $this->hasMany(Renta::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}