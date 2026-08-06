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
                <label class="form-label small fw-semibold text-body">RFC</label>
                <input type="text" name="rfc" id="input_rfc" class="form-control form-control-sm bg-body text-body text-uppercase @error('rfc') is-invalid @enderror" value="{{ old('rfc', $cliente->rfc ?? '') }}" placeholder="12 o 13 caracteres" maxlength="13">
                <div class="invalid-feedback" id="feedback_rfc">Formato de RFC inválido (Ejemplo: VECJ881226XXX o ABC680524P36).</div>
                @error('rfc')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- CURP -->
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">CURP</label>
                <input type="text" name="curp" id="input_curp" class="form-control form-control-sm bg-body text-body text-uppercase @error('curp') is-invalid @enderror" value="{{ old('curp', $cliente->curp ?? '') }}" placeholder="18 caracteres" maxlength="18">
                <div class="invalid-feedback" id="feedback_curp">El CURP debe tener exactamente 18 caracteres con estructura válida.</div>
                @error('curp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Documento INE / Identificación</label>
                
                <!-- Input para seleccionar archivo -->
                <input type="file" name="ine_documento" id="input_ine" class="form-control form-control-sm bg-body text-body @error('ine_documento') is-invalid @enderror" accept="image/*,application/pdf">
                <small class="text-body-secondary d-block mt-1" style="font-size: 11px;">Formatos: JPG, PNG, PDF (Máx. 5MB)</small>

                <!-- CONTENEDOR DE PREVISUALIZACIÓN AUTOMÁTICA -->
                <div id="contenedor_preview_automatico" class="mt-2 d-none border rounded-3 p-2 bg-body-tertiary">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small">
                            <i class="bi bi-file-earmark-arrow-up me-1"></i> Previsualización del archivo
                        </span>
                        <button type="button" id="btn_cancelar_nuevo" class="btn-close btn-sm" aria-label="Cancelar selección" title="Quitar archivo"></button>
                    </div>
                    
                    <!-- Previsualización si es Imagen -->
                    <img id="preview_img" src="" class="img-fluid rounded border d-none" style="max-height: 250px; width: 100%; object-fit: contain;">

                    <!-- Previsualización si es PDF -->
                    <iframe id="preview_pdf" src="" class="w-100 rounded border d-none" style="height: 250px;"></iframe>
                </div>

                @error('ine_documento')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- SECCIÓN 3: DIRECCIÓN -->
        <div class="section-title">
            <i class="bi bi-geo-alt me-2"></i>Dirección
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label small fw-semibold text-body">Calle y Número</label>
                <textarea name="direccion" class="form-control form-control-sm bg-body text-body" rows="2" placeholder="Calle, Número exterior/interior, Colonia">{{ old('direccion') }}</textarea>
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

    // 4. Validación de RFC (Opcional)
    if (inputRFC) {
        inputRFC.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            validarCampo(this, regexRFC.test(this.value), true);
        });
    }

    // 5. Validación de CURP (Opcional)
    if (inputCURP) {
        inputCURP.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            validarCampo(this, regexCURP.test(this.value), true);
        });
    }

    // =========================================================
    // LÓGICA DE PREVISUALIZACIÓN AUTOMÁTICA DE ARCHIVO
    // =========================================================
    const inputIne = document.getElementById('input_ine');
    const contenedorPreview = document.getElementById('contenedor_preview_automatico');
    const previewImg = document.getElementById('preview_img');
    const previewPdf = document.getElementById('preview_pdf');
    const btnCancelarNuevo = document.getElementById('btn_cancelar_nuevo');

    let archivoUrlTemporal = null;

    // Limpiar vista previa y revocar URL del objeto para liberar memoria
    function limpiarPrevisualizacion() {
        if (archivoUrlTemporal) {
            URL.revokeObjectURL(archivoUrlTemporal);
            archivoUrlTemporal = null;
        }
        if (previewImg) { previewImg.src = ''; previewImg.classList.add('d-none'); }
        if (previewPdf) { previewPdf.src = ''; previewPdf.classList.add('d-none'); }
        if (contenedorPreview) contenedorPreview.classList.add('d-none');
    }

    // Evento al seleccionar o cambiar de archivo
    if (inputIne) {
        inputIne.addEventListener('change', function (e) {
            const file = e.target.files[0];
            limpiarPrevisualizacion();

            if (file) {
                archivoUrlTemporal = URL.createObjectURL(file);

                if (file.type.startsWith('image/')) {
                    // Muestra la imagen en el tag <img>
                    previewImg.src = archivoUrlTemporal;
                    previewImg.classList.remove('d-none');
                    contenedorPreview.classList.remove('d-none');
                } else if (file.type === 'application/pdf') {
                    // Muestra el PDF dentro del <iframe>
                    previewPdf.src = archivoUrlTemporal;
                    previewPdf.classList.remove('d-none');
                    contenedorPreview.classList.remove('d-none');
                }
            }
        });
    }

    // Evento para desmarcar/limpiar el archivo seleccionado con la botonera "X"
    if (btnCancelarNuevo) {
        btnCancelarNuevo.addEventListener('click', function () {
            inputIne.value = '';
            limpiarPrevisualizacion();
        });
    }

    // Validación general al enviar el formulario
    if (form) {
        form.addEventListener('submit', function (event) {
            let formValido = true;

            if (inputTel && !validarCampo(inputTel, regexTel.test(inputTel.value))) formValido = false;
            if (inputTelAlt && !validarCampo(inputTelAlt, regexTel.test(inputTelAlt.value), true)) formValido = false;
            if (inputEmail && !validarCampo(inputEmail, regexEmail.test(inputEmail.value), true)) formValido = false;
            if (inputRFC && !validarCampo(inputRFC, regexRFC.test(inputRFC.value), true)) formValido = false;
            if (inputCURP && !validarCampo(inputCURP, regexCURP.test(inputCURP.value), true)) formValido = false;

            if (!formValido) {
                event.preventDefault();
                event.stopPropagation();
                
                // Enfocar el primer campo inválido
                const primerError = form.querySelector('.is-invalid');
                if (primerError) primerError.focus();
            }
        });
    }
});
</script>
@endsection