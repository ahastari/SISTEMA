<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    protected $fillable = [
        'nombre', 'direccion', 'colonia', 'ciudad', 'estado', 
        'codigo_postal', 'telefono_obra', 'contacto_obra', 
        'cliente_id', 'observaciones', 'activa'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function rentas()  // ← Verifica que esta relación exista
    {
        return $this->hasMany(Renta::class);
    }
}