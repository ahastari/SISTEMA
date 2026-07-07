<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IniciarSucursalSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // Forzamos a que revise la sucursal en cada inicio de sesión fresco o si no tiene la variable
        if (Auth::check() && !session()->has('activo_sucursal_id')) {
            $user = Auth::user();

            // 1. Si el usuario (sea admin o no) tiene una sucursal asignada en su perfil, usamos esa.
            if ($user->sucursal) {
                session(['activo_sucursal_id' => $user->sucursal_id]);
                session(['activo_sucursal_nombre' => $user->sucursal->nombre]);
            } 
            // 2. Si NO tiene sucursal asignada pero tiene el rol de administrador global
            elseif ($user->isAdmin()) {
                session(['activo_sucursal_id' => 'global']);
                session(['activo_sucursal_nombre' => 'Consola Global Corporativa']);
            } 
            // 3. Caso de respaldo por si es cajero y quedó volando sin sucursal
            else {
                session(['activo_sucursal_id' => null]);
                session(['activo_sucursal_nombre' => 'Sin Sucursal Asignada']);
            }
        }

        return $next($request);
    }
}