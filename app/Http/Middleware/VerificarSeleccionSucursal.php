<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarSeleccionSucursal
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Ignoramos rutas que no deben ser bloqueadas para evitar bucles
            if ($request->routeIs('sucursal.seleccionar') || 
                $request->routeIs('logout') || 
                $request->routeIs('autorizaciones.notificaciones')) {
                return $next($request);
            }

            $user = auth()->user();
            $sucursales = $user->sucursales;

            // 🛠️ LA SOLUCIÓN: Definimos dinámicamente dónde se debe mostrar el modal
            $rutaDestino = $user->isCajero() ? 'puntoventa.index' : 'dashboard';

            // Si el usuario tiene más de 1 sucursal asignada
            if ($sucursales && $sucursales->count() > 1) {
                
                // Si aún no ha seleccionado sucursal en la sesión
                if (!session()->has('sucursal_seleccionada')) {
                    session(['mostrar_modal_sucursal' => true]);
                    
                    // Si intenta navegar a otra parte sin haber seleccionado, lo forzamos a su vista principal
                    if (!$request->routeIs($rutaDestino) && !$request->expectsJson()) {
                        return redirect()->route($rutaDestino);
                    }
                }
            } elseif ($sucursales && $sucursales->count() === 1) {
                // Si solo tiene 1 sucursal, la configuramos en sesión automáticamente y no lo molestamos
                $unicaSucursalId = $sucursales->first()->id;
                
                if ($user->sucursal_id !== $unicaSucursalId) {
                    $user->sucursal_id = $unicaSucursalId;
                    $user->save();
                }
                
                session([
                    'sucursal_seleccionada' => true,
                    'activo_sucursal_id' => $unicaSucursalId
                ]);
                session()->forget('mostrar_modal_sucursal');
            }
        }

        return $next($request);
    }
}