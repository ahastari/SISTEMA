<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Campos autorizados para asignación masiva.
     */
    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role',
        'sucursal_id',
        'status',
        'foto'
    ];

    /**
     * Atributos ocultos para serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Helpers profesionales para verificar roles.
     */
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

    /**
     * Verifica si el usuario está activo.
     */ 
    public function isActivo(): bool
    {
        return $this->status === 'activo';
    }

    /**
     * Relación inversa: Un usuario pertenece a una sucursal.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Obtener el nombre de la sucursal asignada.
     */
    public function getNombreSucursalAttribute(): string
    {
        return $this->sucursal ? $this->sucursal->nombre : 'Sin sucursal';
    }

    /**
     * Casts de atributos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}