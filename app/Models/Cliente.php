<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre_completo', 'telefono', 'email', 'rfc', 'curp',
        'ine_numero', 'ine_documento', 'contrato_firmado', 'comprobante_deposito',
        'telefono_alternativo', 'empresa', 'direccion', 'ciudad',
        'estado', 'codigo_postal', 'observaciones'
    ];
    
    public function rentas()
    {
        return $this->hasMany(Renta::class);
    }
    
    public function obras()
    {
        return $this->hasMany(Obra::class);
    }
    
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

}