@extends('layouts.admin')

@section('content')
<style>
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }
    .form-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color) !important;
        border-radius: 16px;
    }
    .section-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--bs-primary);
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--bs-border-color);
    }
</style>

<!-- Header de la página -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-person-plus-fill text-primary me-2"></i>Nuevo Cliente
        </h2>
        <p class="text-body-secondary small mb-0">Registra un nuevo cliente en el sistema para gestionar contratos y rentas.</p>
    </div>
    <div>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-left me-1"></i> Regresar
        </a>
    </div>
</div>

<!-- ALERTAS DE VALIDACIÓN Y BLOQUEO -->
@if($errors->has('global_block'))
    <div class="alert alert-danger shadow-sm border-danger d-flex align-items-center mb-4">
        <i class="bi bi-shield-fill-x fs-1 me-3 text-danger"></i>
        <div>
            <h5 class="mb-1 fw-bold text-danger">REGISTRO DENEGADO (LISTA NEGRA)</h5>
            <p class="mb-0">{{ $errors->first('global_block') }}</p>
        </div>
    </div>
@elseif($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center mb-1">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <strong class="text-danger">Corrige los siguientes errores antes de continuar:</strong>
        </div>
        <ul class="mb-0 small ps-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="card form-card shadow-sm p-3 p-md-4 mb-4">
        
        <!-- SECCIÓN 1: INFORMACIÓN PERSONAL Y CONTACTO -->
        <div class="section-title">
            <i class="bi bi-person-vcard me-2"></i>Información Personal y Contacto
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-body">Nombre Completo <span class="text-danger">*</span></label>
                <input type="text" name="nombre_completo" class="form-control form-control-sm bg-body text-body @error('nombre_completo') is-invalid @enderror" value="{{ old('nombre_completo') }}" placeholder="Ej: Juan Pérez González" required>
                @error('nombre_completo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-body">Empresa</label>
                <input type="text" name="empresa" class="form-control form-control-sm bg-body text-body" value="{{ old('empresa') }}" placeholder="Ej: Constructora del Norte S.A.">
            </div>

            <!-- Teléfono Principal -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Teléfono Principal <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0"><i class="bi bi-telephone"></i></span>
                    <input type="text" name="telefono" id="input_telefono" class="form-control bg-body text-body border-start-0 @error('telefono') is-invalid @enderror" value="{{ old('telefono', $cliente->telefono ?? '') }}" placeholder="10 dígitos" maxlength="10" required>
                    <div class="invalid-feedback" id="feedback_telefono">El teléfono debe contener exactamente 10 dígitos numéricos.</div>
                </div>
                @error('telefono')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Teléfono Alternativo -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Teléfono Alternativo</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0"><i class="bi bi-telephone-plus"></i></span>
                    <input type="text" name="telefono_alternativo" id="input_telefono_alt" class="form-control bg-body text-body border-start-0" value="{{ old('telefono_alternativo', $cliente->telefono_alternativo ?? '') }}" placeholder="Opcional (10 dígitos)" maxlength="10">
                    <div class="invalid-feedback" id="feedback_telefono_alt">El teléfono alternativo debe tener 10 dígitos.</div>
                </div>
            </div>

            <!-- Correo Electrónico -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Correo Electrónico</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="input_email" class="form-control bg-body text-body border-start-0 @error('email') is-invalid @enderror" value="{{ old('email', $cliente->email ?? '') }}" placeholder="correo@ejemplo.com">
                    <div class="invalid-feedback" id="feedback_email">Ingresa un correo electrónico válido.</div>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- SECCIÓN 2: IDENTIFICACIÓN Y FISCAL -->
        <div class="section-title">
            <i class="bi bi-file-earmark-person me-2"></i>Identificación y Datos Fiscales
        </div>
        <div class="row g-3 mb-4">
            <!-- RFC -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">RFC <span class="text-danger">*</span></label>
                <input type="text" name="rfc" id="input_rfc" class="form-control form-control-sm bg-body text-body text-uppercase @error('rfc') is-invalid @enderror" value="{{ old('rfc', $cliente->rfc ?? '') }}" placeholder="12 o 13 caracteres" maxlength="13" required>
                <div class="invalid-feedback" id="feedback_rfc">Formato de RFC inválido (Ejemplo: VECJ881226XXX o ABC680524P36).</div>
                @error('rfc')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- CURP -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">CURP <span class="text-danger">*</span></label>
                <input type="text" name="curp" id="input_curp" class="form-control form-control-sm bg-body text-body text-uppercase @error('curp') is-invalid @enderror" value="{{ old('curp', $cliente->curp ?? '') }}" placeholder="18 caracteres" maxlength="18" required>
                <div class="invalid-feedback" id="feedback_curp">El CURP debe tener exactamente 18 caracteres con estructura válida.</div>
                @error('curp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Documento INE / Identificación</label>
                <input type="file" name="ine_documento" id="input_ine" class="form-control form-control-sm bg-body text-body @error('ine_documento') is-invalid @enderror" accept="image/*,application/pdf">
                <small class="text-body-secondary d-block mt-1" style="font-size: 11px;">Formatos: JPG, PNG, PDF (Máx. 5MB)</small>

                <div id="preview_container_ine" class="mt-2 d-none border rounded-3 p-2 bg-body-tertiary position-relative">
                    <span id="label_ine" class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small mb-2 d-inline-block"></span>
                    <button type="button" class="btn-close btn-sm position-absolute top-0 end-0 m-2" onclick="limpiarArchivo('ine')" title="Quitar archivo"></button>
                    
                    <img id="img_ine" src="" class="img-fluid rounded border d-none" style="max-height: 180px; width: 100%; object-fit: contain;">
                    <iframe id="pdf_ine" src="" class="w-100 rounded border d-none" style="height: 180px;"></iframe>
                </div>
                @error('ine_documento') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- SECCIÓN 3: DIRECCIÓN -->
        <div class="section-title">
            <i class="bi bi-geo-alt me-2"></i>Dirección
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label small fw-semibold text-body">Calle y Número</label>
                <textarea name="direccion" class="form-control form-control-sm bg-body text-body" rows="2" placeholder="Calle, Número exterior/interior">{{ old('direccion') }}</textarea>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Colonia / Fraccionamiento</label>
                <input type="text" name="colonia" class="form-control form-control-sm bg-body text-body" value="{{ old('colonia') }}" placeholder="Ej: Zona Centro">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Ciudad / Municipio</label>
                <input type="text" name="ciudad" class="form-control form-control-sm bg-body text-body" value="{{ old('ciudad') }}" placeholder="Ej: Durango">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Estado</label>
                <input type="text" name="estado" class="form-control form-control-sm bg-body text-body" value="{{ old('estado') }}" placeholder="Ej: Durango">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Código Postal</label>
                <input type="text" name="codigo_postal" class="form-control form-control-sm bg-body text-body" value="{{ old('codigo_postal') }}" placeholder="Ej: 34000">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Comprobante de Domicilio</label>
                <input type="file" name="comprobante_domicilio_path" id="input_comprobante" class="form-control form-control-sm bg-body text-body" accept="image/*,application/pdf">
                <small class="text-body-secondary d-block mt-1" style="font-size: 11px;">Formatos: JPG, PNG, PDF (Máx. 5MB)</small>

                <div id="preview_container_comprobante" class="mt-2 d-none border rounded-3 p-2 bg-body-tertiary position-relative">
                    <span id="label_comprobante" class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small mb-2 d-inline-block"></span>
                    <button type="button" class="btn-close btn-sm position-absolute top-0 end-0 m-2" onclick="limpiarArchivo('comprobante')" title="Quitar archivo"></button>
                    
                    <img id="img_comprobante" src="" class="img-fluid rounded border d-none" style="max-height: 180px; width: 100%; object-fit: contain;">
                    <iframe id="pdf_comprobante" src="" class="w-100 rounded border d-none" style="height: 180px;"></iframe>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 4: OBSERVACIONES -->
        <div class="section-title">
            <i class="bi bi-chat-left-text me-2"></i>Observaciones
        </div>
        <div class="row g-3">
            <div class="col-12">
                <textarea name="observaciones" class="form-control form-control-sm bg-body text-body" rows="3" placeholder="Notas adicionales sobre el cliente o referencias...">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <hr class="my-4 border-secondary-subtle">

        <!-- Botones de Acción -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary btn-sm rounded-3 px-4">Cancelar</a>
            <button type="submit" class="btn btn-success btn-sm fw-bold rounded-3 px-4 shadow-sm">
                <i class="bi bi-check-lg me-1"></i> Guardar Cliente
            </button>
        </div>

    </div>
</form>

<script>
    
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    // Expresiones Regulares de Validación
    const regexTel = /^\d{10}$/;
    const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const regexRFC = /^([A-Z&Ñ]{3,4})\d{6}([A-Z0-9]{3})$/i;
    const regexCURP = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/i;

    // Elementos de Entrada
    const inputTel = document.getElementById('input_telefono');
    const inputTelAlt = document.getElementById('input_telefono_alt');
    const inputEmail = document.getElementById('input_email');
    const inputRFC = document.getElementById('input_rfc');
    const inputCURP = document.getElementById('input_curp');

    // Helper para aplicar / quitar estados de Bootstrap
    function validarCampo(input, esValido, esOpcional = false) {
        if (esOpcional && input.value.trim() === '') {
            input.classList.remove('is-invalid', 'is-valid');
            return true;
        }

        if (esValido) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            return true;
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            return false;
        }
    }

    // 1. Validación de Teléfono (Requerido)
    if (inputTel) {
        inputTel.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, ''); // Restringir solo a números
            validarCampo(this, regexTel.test(this.value));
        });
    }

    // 2. Validación de Teléfono Alternativo (Opcional)
    if (inputTelAlt) {
        inputTelAlt.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            validarCampo(this, regexTel.test(this.value), true);
        });
    }

    // 3. Validación de Correo Electrónico (Opcional)
    if (inputEmail) {
        inputEmail.addEventListener('input', function() {
            validarCampo(this, regexEmail.test(this.value), true);
        });
    }

    // 4. Validación de RFC
    if (inputRFC) {
        inputRFC.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            validarCampo(this, regexRFC.test(this.value), false);
        });
    }

    // 5. Validación de CURP
    if (inputCURP) {
        inputCURP.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            validarCampo(this, regexCURP.test(this.value), false);
        });
    }
});

// =========================================================
// LÓGICA DE PREVISUALIZACIÓN AUTOMÁTICA Y ELIMINACIÓN
// =========================================================

function setupFilePreview(key) {
    const input = document.getElementById(`input_${key}`);
    const container = document.getElementById(`preview_container_${key}`);
    const img = document.getElementById(`img_${key}`);
    const pdf = document.getElementById(`pdf_${key}`);
    const hiddenEliminar = document.getElementById(`eliminar_${key}`);
    const label = document.getElementById(`label_${key}`);

    if (input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            // Si elige uno nuevo, cancelamos la eliminación del anterior
            if (hiddenEliminar) hiddenEliminar.value = "0";

            if (file) {
                const url = URL.createObjectURL(file);
                container.classList.remove('d-none');
                
                if (label) {
                    label.innerHTML = '<i class="bi bi-file-earmark-arrow-up me-1"></i> Previsualización de nuevo archivo';
                }

                if (file.type.startsWith('image/')) {
                    img.src = url;
                    img.classList.remove('d-none');
                    pdf.classList.add('d-none');
                } else if (file.type === 'application/pdf') {
                    pdf.src = url;
                    pdf.classList.remove('d-none');
                    img.classList.add('d-none');
                }
            } else {
                limpiarArchivo(key);
            }
        });
    }
}

// Función que limpia la vista y marca para eliminación
window.limpiarArchivo = function(key) {
    const input = document.getElementById(`input_${key}`);
    const container = document.getElementById(`preview_container_${key}`);
    const img = document.getElementById(`img_${key}`);
    const pdf = document.getElementById(`pdf_${key}`);
    const hiddenEliminar = document.getElementById(`eliminar_${key}`);

    if(input) input.value = '';
    if(container) container.classList.add('d-none');
    if(img) { img.src = ''; img.classList.add('d-none'); }
    if(pdf) { pdf.src = ''; pdf.classList.add('d-none'); }
    
    // Si estamos editando y cerramos, mandamos un "1" al backend para borrar el archivo
    if (hiddenEliminar) {
        hiddenEliminar.value = "1";
    }
};

// Inicializamos ambos campos
setupFilePreview('ine');
setupFilePreview('comprobante');
</script>
@endsection