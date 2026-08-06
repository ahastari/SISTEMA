<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // 🛠️ LÓGICA MULTISUCURSAL PROFESIONAL INTEGRADA
        $user = Auth::user();
        
        // Carga la relación 'sucursal' explícitamente para evitar problemas de memoria
        if ($user->isAdmin()) {
            // Si es Admin Global, por defecto tiene acceso completo a todas las sucursales
            session(['activo_sucursal_id' => 'global']);
            session(['activo_sucursal_nombre' => 'Consola Global Corporativa']);
        } else {
            // Si es Gerente o Cajero, lo amarramos estrictamente a su sucursal asignada
            if ($user->sucursal) {
                session(['activo_sucursal_id' => $user->sucursal_id]);
                session(['activo_sucursal_nombre' => $user->sucursal->nombre]);
            } else {
                // Respaldo UX: Evita que el sistema rompa si no tiene sucursal asignada aún
                session(['activo_sucursal_id' => null]);
                session(['activo_sucursal_nombre' => 'Sin Sucursal Asignada']);
            }
        }

        if ($user->isCajero()) {
            // El cajero va directo a la pantalla de cobro
            return redirect()->intended(route('puntoventa.index', absolute: false));
        }

        // Administradores y Gerentes van al Dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}