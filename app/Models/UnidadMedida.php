<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidades_medida';
    
    protected $fillable = [
        'nombre', 'abreviatura', 'activa'
    ];

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }
}