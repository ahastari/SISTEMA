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
            <i class="bi bi-building-add text-primary me-2"></i>Nueva Obra
        </h2>
        <p class="text-body-secondary small mb-0">Registra un nuevo proyecto o dirección de entrega asociada a un cliente.</p>
    </div>
    <div>
        <a href="{{ route('obras.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-left me-1"></i> Regresar
        </a>
    </div>
</div>

<form action="{{ route('obras.store') }}" method="POST">
    @csrf
    
    <div class="card form-card shadow-sm p-3 p-md-4 mb-4">
        
        <!-- SECCIÓN 1: DATOS GENERALES -->
        <div class="section-title">
            <i class="bi bi-building me-2"></i>Información General de la Obra
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-body">Nombre de la Obra / Proyecto <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control form-control-sm bg-body text-body @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" placeholder="Ej: Residencial Los Arboles - Lote 12" required>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-body">Cliente Asociado <span class="text-danger">*</span></label>
                <select name="cliente_id" class="form-select form-select-sm bg-body text-body @error('cliente_id') is-invalid @enderror" required>
                    <option value="">Seleccionar cliente...</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nombre_completo }} {{ $cliente->empresa ? '('.$cliente->empresa.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('cliente_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- SECCIÓN 2: UBICACIÓN Y DIRECCIÓN -->
        <div class="section-title">
            <i class="bi bi-geo-alt me-2"></i>Ubicación y Dirección de Entrega
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label small fw-semibold text-body">Calle y Número <span class="text-danger">*</span></label>
                <textarea name="direccion" class="form-control form-control-sm bg-body text-body @error('direccion') is-invalid @enderror" rows="2" placeholder="Calle, Número exterior/interior, Referencias" required>{{ old('direccion') }}</textarea>
                @error('direccion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-body">Colonia</label>
                <input type="text" name="colonia" class="form-control form-control-sm bg-body text-body" value="{{ old('colonia') }}" placeholder="Ej: Centro">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-body">Ciudad / Municipio</label>
                <input type="text" name="ciudad" class="form-control form-control-sm bg-body text-body" value="{{ old('ciudad', 'Durango') }}" placeholder="Ej: Durango">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-body">Estado</label>
                <input type="text" name="estado" class="form-control form-control-sm bg-body text-body" value="{{ old('estado', 'Dgo.') }}" placeholder="Ej: Durango">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-body">Código Postal</label>
                <input type="text" name="codigo_postal" class="form-control form-control-sm bg-body text-body" value="{{ old('codigo_postal') }}" placeholder="Ej: 34000">
            </div>
        </div>

        <!-- SECCIÓN 3: CONTACTO Y ESTADO -->
        <div class="section-title">
            <i class="bi bi-person-lines-fill me-2"></i>Contacto en Obra y Estatus
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Teléfono de la Obra</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0"><i class="bi bi-telephone"></i></span>
                    <input type="text" name="telefono_obra" id="input_telefono_obra" class="form-control bg-body text-body border-start-0" value="{{ old('telefono_obra') }}" placeholder="Opcional (10 dígitos)" maxlength="10">
                    <div class="invalid-feedback" id="feedback_telefono_obra">El teléfono de la obra debe contener exactamente 10 dígitos numéricos.</div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-body">Contacto / Encargado en Obra</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0"><i class="bi bi-person"></i></span>
                    <input type="text" name="contacto_obra" class="form-control bg-body text-body border-start-0" value="{{ old('contacto_obra') }}" placeholder="Ej: Ing. Carlos Ruiz">
                </div>
            </div>

            <div class="col-12 col-md-4 d-flex align-items-center">
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="activa" id="activa" value="1" {{ old('activa', '1') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold text-body small" for="activa">Obra activa (Disponible para asignación de rentas)</label>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 4: OBSERVACIONES -->
        <div class="section-title">
            <i class="bi bi-chat-left-text me-2"></i>Observaciones
        </div>
        <div class="row g-3">
            <div class="col-12">
                <textarea name="observaciones" class="form-control form-control-sm bg-body text-body" rows="3" placeholder="Notas sobre accesos, restricciones de horario, encargados de recepción...">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <hr class="my-4 border-secondary-subtle">

        <!-- Botones de Acción -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('obras.index') }}" class="btn btn-secondary btn-sm rounded-3 px-4">Cancelar</a>
            <button type="submit" class="btn btn-success btn-sm fw-bold rounded-3 px-4 shadow-sm">
                <i class="bi bi-check-lg me-1"></i> Guardar Obra
            </button>
        </div>

    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const inputTelObra = document.getElementById('input_telefono_obra');
    const regexTel = /^\d{10}$/;

    function validarCampo(input, esValido, esOpcional = true) {
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

    // Restringir a números y validar en tiempo real
    if (inputTelObra) {
        inputTelObra.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, ''); // Eliminar todo lo que no sea número
            validarCampo(this, regexTel.test(this.value), true);
        });
    }

    // Evitar envío si el teléfono opcional no cumple los 10 dígitos
    if (form) {
        form.addEventListener('submit', function (e) {
            if (inputTelObra && !validarCampo(inputTelObra, regexTel.test(inputTelObra.value), true)) {
                e.preventDefault();
                e.stopPropagation();
                inputTelObra.focus();
            }
        });
    }
});
</script>
@endsection