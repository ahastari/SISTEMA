<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Models\PlantillaDocumento;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Cache;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $sucursales = Sucursal::all();
            $usuarios = User::with('sucursal')->get();
        } elseif ($user->isGerente()) {
            $sucursales = Sucursal::where('id', $user->sucursal_id)->get();
            $usuarios = collect();
        } else {
            abort(403, 'Acceso denegado');
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

    public function updateEmpresa(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para modificar los datos de la empresa.');
        }

        $request->validate([
            'empresa_nombre' => 'required|string|max:255',
            'empresa_dueno' => 'nullable|string|max:255',
            'empresa_direccion' => 'nullable|string|max:500',
            'empresa_rfc' => ['nullable', 'string', 'regex:/^([A-ZÑ&]{3,4}) ?(?:- ?)?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])) ?(?:- ?)?([A-Z\d]{2})([A\d])$/i'],
            'empresa_telefono' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'empresa_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $campos = $request->only(['empresa_nombre', 'empresa_dueno', 'empresa_direccion', 'empresa_rfc', 'empresa_telefono']);
        
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
}