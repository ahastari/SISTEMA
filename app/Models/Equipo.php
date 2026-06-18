<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';
    
    protected $fillable = [
        'codigo', 'nombre', 'categoria_id', 'unidad_medida_id',
        'precio_dia', 'stock', 'imagen', 'descripcion', 'activo'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }
}