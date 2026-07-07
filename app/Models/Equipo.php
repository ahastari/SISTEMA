<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';
    
    protected $fillable = [
        'codigo', 'codigo_barras', 'nombre', 'categoria_id', 'unidad_medida_id', 'tipo_operacion',
        'precio_dia', 'precio_venta', 'stock', 'stock_minimo', 'imagen', 'descripcion', 'activo'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class);
    }
    
}