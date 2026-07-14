<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\PlantillaDocumento;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Cache;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // 🔒 FILTRADO POR SUCURSAL SEGÚN ROL
        if ($user->isAdmin()) {
            // Admin ve todas las sucursales y usuarios
            $sucursales = Sucursal::all();
            $usuarios = User::with('sucursal')->get();
        } elseif ($user->isGerente()) {
            // Gerente SOLO ve su sucursal asignada
            $sucursales = Sucursal::where('id', $user->sucursal_id)->get();
            // Por seguridad, los gerentes NO ven la lista de usuarios
            $usuarios = collect();
        } else {
            // Cajero: sin acceso a configuración (redirigir o mostrar vacío)
            $sucursales = collect();
            $usuarios = collect();
        }
        
        $plantillas = PlantillaDocumento::all();
        
        return view('configuracion.configuracion', compact('sucursales', 'usuarios', 'plantillas'));
    }

    public function updatePlantilla(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string'
        ]);

        $plantilla = PlantillaDocumento::findOrFail($id);
        $plantilla->update($request->only(['titulo', 'contenido']));

        return redirect()->back()->with(['success' => 'Estructura del documento actualizada con éxito.', 'tab' => 'plantillas']);
    }

    /**
     * ACTUALIZAR DATOS DE LA EMPRESA (SOLO ADMIN)
     */
    public function updateEmpresa(Request $request)
    {
        // Verificación explícita de rol
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para modificar los datos de la empresa.');
        }

        $request->validate([
            'empresa_nombre' => 'required|string|max:255',
            'empresa_direccion' => 'nullable|string|max:500',
            'empresa_rfc' => 'nullable|string|max:20',
            'empresa_telefono' => 'nullable|string|max:20',
            'empresa_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $campos = $request->only(['empresa_nombre', 'empresa_direccion', 'empresa_rfc', 'empresa_telefono']);
        
        foreach ($campos as $key => $value) {
            Configuracion::set($key, $value);
            Cache::forget("config_{$key}");
        }

        if ($request->hasFile('empresa_logo')) {
            $logoActual = Configuracion::where('key', 'empresa_logo')->first();
            if ($logoActual && $logoActual->value && Storage::disk('public')->exists($logoActual->value)) {
                Storage::disk('public')->delete($logoActual->value);
            }

            $path = $request->file('empresa_logo')->store('empresa', 'public');
            Configuracion::set('empresa_logo', $path);
            Cache::forget('config_empresa_logo');
        }

        return redirect()->back()->with(['success' => 'Información corporativa actualizada correctamente.', 'tab' => 'empresa']);
    }

    /**
     * CREAR SUCURSAL (SOLO ADMIN)
     */
    public function storeSucursal(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para crear sucursales.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:500',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $sucursal = new Sucursal($request->except('logo'));

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('sucursales', 'public');
            $sucursal->logo = $path;
        }
        $sucursal->save();

        return redirect()->back()->with(['success' => 'Nueva sucursal dada de alta correctamente.', 'tab' => 'sucursales']);
    }
}