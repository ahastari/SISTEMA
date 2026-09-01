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

        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN ESPECIAL DEL PAGARÉ
        |--------------------------------------------------------------------------
        */
        $pagareKeys = [
            // Encabezado
            'pagare_titulo',
            'pagare_texto_bueno_por',
            'pagare_etiqueta_numero',
            'pagare_numero',
            'pagare_texto_en',
            'pagare_texto_a',
            'pagare_texto_de_mes',
            'pagare_texto_de_anio',
            'pagare_etiqueta_expedicion',

            // Cuerpo
            'pagare_texto_promesa',
            'pagare_etiqueta_beneficiario',
            'pagare_texto_lugar',
            'pagare_etiqueta_lugar_pago',
            'pagare_etiqueta_fecha_pago',
            'pagare_texto_cantidad',
            'pagare_texto_porcentaje',
            'pagare_clausula_legal',
            'pagare_valor_beneficiario',
            'pagare_valor_lugar_pago',
            'pagare_valor_fecha_pago',
            'pagare_valor_monto',
            'pagare_valor_monto_letras',
            'pagare_texto_importe',

            // Deudor
            'pagare_titulo_deudor',
            'pagare_etiqueta_nombre',
            'pagare_etiqueta_direccion',
            'pagare_etiqueta_poblacion',
            'pagare_etiqueta_telefono',

            // Firma
            'pagare_texto_acepto',
            'pagare_texto_firma',
            'pagare_texto_pie',

            // Apariencia
            'pagare_color_principal',
            'pagare_color_fondo',
            'pagare_tamano_texto',

            'pagare_valor_numero',
            'pagare_valor_importe',

            'pagare_valor_lugar_expedicion',
            'pagare_valor_dia_expedicion',
            'pagare_valor_mes_expedicion',
            'pagare_valor_anio_expedicion',
            ];

        $pagareConfig = Configuracion::whereIn('key', $pagareKeys)
            ->pluck('value', 'key')
            ->toArray();

        return view(
            'configuracion.configuracion',
            compact(
                'sucursales',
                'usuarios',
                'plantillas',
                'pagareConfig'
            )
        );
    }

    public function updatePlantilla(Request $request, $id)
    {
        $plantilla = PlantillaDocumento::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | PAGARÉ - CONFIGURACIÓN AVANZADA
        |--------------------------------------------------------------------------
        */
        if ($plantilla->tipo === 'pagare') {

            $request->validate([
                'titulo' => 'required|string|max:255',

                'pagare_titulo' => 'required|string|max:255',
                'pagare_texto_bueno_por' => 'required|string|max:100',
                'pagare_etiqueta_numero' => 'required|string|max:50',
                'pagare_valor_numero' => 'required|string|max:20',

                'pagare_texto_promesa' => 'required|string|max:1000',
                'pagare_clausula_legal' => 'required|string|max:5000',

                'pagare_color_principal' => 'nullable|string|max:20',
                'pagare_color_fondo' => 'nullable|string|max:20',
                'pagare_tamano_texto' => 'nullable|numeric|min:8|max:16',
            ]);

            /*
            |--------------------------------------------------------------------------
            | TÍTULO DE LA PLANTILLA
            |--------------------------------------------------------------------------
            */
            $plantilla->titulo = $request->titulo;

            // Seguimos utilizando contenido para la cláusula principal.
            $plantilla->contenido = $request->pagare_clausula_legal;

            $plantilla->save();

            /*
            |--------------------------------------------------------------------------
            | TODOS LOS CAMPOS EDITABLES DEL PAGARÉ
            |--------------------------------------------------------------------------
            */
            $campos = [
                'pagare_titulo',
                'pagare_texto_bueno_por',
                'pagare_etiqueta_numero',
                'pagare_numero',

                'pagare_texto_en',
                'pagare_texto_a',
                'pagare_texto_de_mes',
                'pagare_texto_de_anio',
                'pagare_etiqueta_expedicion',

                'pagare_texto_promesa',
                'pagare_etiqueta_beneficiario',

                'pagare_texto_lugar',
                'pagare_etiqueta_lugar_pago',
                'pagare_etiqueta_fecha_pago',

                'pagare_texto_cantidad',
                'pagare_texto_porcentaje',

                'pagare_clausula_legal',

                'pagare_titulo_deudor',
                'pagare_etiqueta_nombre',
                'pagare_etiqueta_direccion',
                'pagare_etiqueta_poblacion',
                'pagare_etiqueta_telefono',

                'pagare_texto_acepto',
                'pagare_texto_firma',
                'pagare_texto_pie',

                'pagare_color_principal',
                'pagare_color_fondo',
                'pagare_tamano_texto',

                'pagare_valor_beneficiario',
                'pagare_valor_lugar_pago',
                'pagare_valor_fecha_pago',

                'pagare_valor_monto',
                'pagare_valor_monto_letras',
                'pagare_texto_importe',
                'pagare_valor_numero',
                'pagare_valor_importe',

                'pagare_valor_lugar_expedicion',
                'pagare_valor_dia_expedicion',
                'pagare_valor_mes_expedicion',
                'pagare_valor_anio_expedicion',
            ];

            foreach ($campos as $campo) {

                if ($request->has($campo)) {
                    Configuracion::set(
                        $campo,
                        $request->input($campo)
                    );

                    Cache::forget("config_{$campo}");
                }
            }

            return redirect()
                ->back()
                ->with([
                    'success' => 'Configuración del pagaré actualizada correctamente.',
                    'tab' => 'plantillas'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | RESTO DE DOCUMENTOS - CONTRATO
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string'
        ]);

        $plantilla->update(
            $request->only([
                'titulo',
                'contenido'
            ])
        );

        return redirect()
            ->back()
            ->with([
                'success' => 'Estructura del documento actualizada con éxito.',
                'tab' => 'plantillas'
            ]);
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