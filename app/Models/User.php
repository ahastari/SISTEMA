<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Campos autorizados para asignación masiva desde tu ConfiguracionController
    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role',          // Sincronizado con el controlador ('admin', 'gerente', 'cajero')
        'sucursal_id',    // Enlace multisucursal obligatorio
        'status',        // 'activo' o 'baja'
        'foto'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Helpers profesionales para verificar roles en vistas o políticas de seguridad
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGerente(): bool
    {
        return $this->role === 'gerente';
    }

    public function isCajero(): bool
    {
        return $this->role === 'cajero';
    }

    /**
     * Relación inversa: Un usuario pertenece a una sucursal.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}