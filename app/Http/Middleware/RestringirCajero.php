<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestringirCajero
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Si el usuario autenticado es un CAJERO
        if ($user && $user->isCajero()) {
            
            // 1. Lista BLANCA de rutas permitidas (Basado en el menú lateral)
            $rutasPermitidas = [
                'puntoventa.*',        // Módulo Punto de Venta
                'rentas.*',            // Módulo Rentas
                'inventario.*',        // Módulo Inventario
                'movimientos.*',       // Módulo Movimientos
                'obras.*',             // Módulo Obras
                'get.obras',           // Endpoint AJAX para obras
                'clientes.*',          // Módulo Clientes
                'categorias.*',        // Necesario para crear/editar productos
                'unidades.*',          // Necesario para crear/editar productos
                'profile.*',           // Editar su perfil
                'logout',              // Cerrar sesión
                'sucursal.seleccionar' // Cambiar de sucursal en el modal
            ];

            // 2. Comprobamos si la ruta actual está en su lista de permisos
            foreach ($rutasPermitidas as $ruta) {
                if ($request->routeIs($ruta)) {
                    return $next($request); // Tiene permiso, lo dejamos pasar
                }
            }

            // 3. Si intenta entrar a 'dashboard' o cualquier otra URL no listada arriba,
            // lo bloqueamos y lo regresamos al punto de venta.
            return redirect()->route('puntoventa.index')
                             ->with('error', 'No tienes permiso para acceder al Panel de Control.');
        }

        // Si es Administrador o Gerente, tienen acceso total
        return $next($request);
    }
}