<?php

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
}