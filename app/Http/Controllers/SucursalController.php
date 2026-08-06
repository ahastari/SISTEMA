<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SucursalController extends Controller
{
    private function reglasValidacion($isUpdate = false)
    {
        return [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'rfc' => [
                'nullable', 
                'string', 
                'regex:/^([A-ZÑ&]{3,4})\d{6}([A-Z0-9]{3})$/i' 
            ],
            'telefono' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'activa' => $isUpdate ? 'required|boolean' : 'nullable'
        ];
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para crear sucursales.');
        }

        $request->validate($this->reglasValidacion(false));

        $sucursal = new Sucursal($request->except('logo'));
        $sucursal->activa = 1;

        if ($request->hasFile('logo')) {
            $sucursal->logo = $request->file('logo')->store('sucursales', 'public');
        }
        
        $sucursal->save();

        return redirect()->back()->with(['success' => 'Nueva sucursal dada de alta correctamente.', 'tab' => 'sucursales']);
    }

    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $user = auth()->user();

        if ($user->isGerente() && $sucursal->id !== $user->sucursal_id) {
            abort(403, 'No tienes permiso para modificar esta sucursal.');
        }

        $request->validate($this->reglasValidacion(true));

        $sucursal->fill($request->except(['logo']));

        if ($request->hasFile('logo')) {
            if ($sucursal->logo && Storage::disk('public')->exists($sucursal->logo)) {
                Storage::disk('public')->delete($sucursal->logo);
            }
            $sucursal->logo = $request->file('logo')->store('sucursales', 'public');
        }
        
        $sucursal->save();

        Cache::forget("sucursal_data_{$sucursal->id}");

        return redirect()->back()->with(['success' => 'Datos de la sucursal actualizados con éxito.', 'tab' => 'sucursales']);
    }
}