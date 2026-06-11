<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'precio_dia',
        'stock',
        'imagen',
        'descripcion',
        'activo'
    ];

    // Esto es importante - Laravel espera 'inventario' como parámetro
    public function getRouteKeyName()
    {
        return 'id';
    }
}