<?php

// app/Models/PlantillaDocumento.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PlantillaDocumento extends Model
{
    protected $table = 'plantillas_documentos';
    protected $fillable = ['tipo', 'titulo', 'contenido'];
}

