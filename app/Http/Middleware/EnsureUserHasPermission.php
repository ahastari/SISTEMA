<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Usuario no autenticado.');
        }

        // El administrador global tiene todos los permisos.
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Evaluamos el permiso específico para roles no administradores.
        if ($this->hasPermission($user, $permission)) {
            return $next($request);
        }

        abort(403, 'No tienes autorización para realizar esta acción.');
    }

    /**
     * Lógica de verificación de permisos.
     */
    private function hasPermission($user, $permission): bool
    {
        return match ($permission) {
            'gestionar_sucursal' => $user->isGerente(),
            'ver_configuracion' => $user->isGerente(),
            'ver_autorizaciones' => $user->isGerente(),
            
            'admin' => false,
            'gestionar_usuarios' => false,
            'gestionar_empresa' => false,
            'crear_sucursal' => false,
            
            // Por defecto, denegar cualquier otro permiso.
            default => false,
        };
    }
}