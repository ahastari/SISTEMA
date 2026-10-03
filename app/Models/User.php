<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role',
        'sucursal_id',
        'status',
        'foto'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAdmin(): bool
    {
        return strtolower($this->role) === 'admin';
    }

    public function isGerente(): bool
    {
        return strtolower($this->role) === 'gerente';
    }

    public function isCajero(): bool
    {
        return strtolower($this->role) === 'cajero';
    }

    public function isActivo(): bool
    {
        return $this->status === 'activo';
    }

    /**
     * Sucursal activa seleccionada para la sesión laboral actual
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Todas las sucursales a las que el usuario tiene acceso
     */
    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_user');
    }

    public function getNombreSucursalAttribute(): string
    {
        return $this->sucursal ? $this->sucursal->nombre : 'Sin sucursal';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}