@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-box-seam me-2 text-primary"></i>Registrar Nuevo Producto
        </h3>
        <p class="text-secondary small mb-0">Agregar un nuevo elemento al catálogo de inventario</p>
    </div>
    <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Regresar
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Por favor corrige los errores:
        <ul class="mb-0 mt-1 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('inventario.store') }}" method="POST" enctype="multipart/form-data" id="formCrearProducto">
    @csrf
    
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            
            <div class="card border-0 shadow-sm rounded-3 mb-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
                <div class="card-header bg-body-tertiary border-bottom py-2 px-3 rounded-top-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-upc-scan me-2"></i>Identificación del Producto</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-12 mb-2">
                            <label class="form-label small fw-semibold text-body">Nombre del equipo / producto <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control form-control-sm bg-body text-body @error('nombre') is-invalid @enderror" 
                                   value="{{ old('nombre') }}" placeholder="Ej: Andamio Estructural de 2x2m" required>
                        </div>
                        
                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label small fw-semibold text-secondary">Código Interno (Auto-generado)</label>
                            <input type="text" class="form-control form-control-sm bg-body-tertiary text-secondary" value="Se asignará automáticamente al guardar" readonly disabled>
                        </div>

                        <div class="col-12 col-md-6 mb-2">
                            <label class="form-label small fw-semibold text-body">Código de Barras (Escáner)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary text-secondary"><i class="bi bi-upc"></i></span>
                                <input type="text" name="codigo_barras" class="form-control bg-body text-body font-monospace @error('codigo_barras') is-invalid @enderror" 
                                       value="{{ old('codigo_barras') }}" placeholder="Clic aquí y usa el escáner">
                            </div>
                            <small class="text-secondary" style="font-size: 11px;">Si el producto tiene código de barras, escanéalo aquí.</small>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-body">Descripción detallada</label>
                            <textarea name="descripcion" class="form-control form-control-sm bg-body text-body" rows="3" placeholder="Medidas, peso, marca, modelo, etc.">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 mb-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
                <div class="card-header bg-body-tertiary border-bottom py-2 px-3 rounded-top-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-cash-coin me-2"></i>Operación y Precios</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body d-block">Tipo de Disponibilidad <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2">
                            <input type="checkbox" class="btn-check" name="operaciones[]" id="op_renta" value="renta" {{ !old('operaciones') || in_array('renta', old('operaciones', [])) ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm rounded-3" for="op_renta"><i class="bi bi-clock-history me-1"></i> Para Renta</label>

                            <input type="checkbox" class="btn-check" name="operaciones[]" id="op_venta" value="venta" {{ is_array(old('operaciones')) && in_array('venta', old('operaciones')) ? 'checked' : '' }}>
                            <label class="btn btn-outline-success btn-sm rounded-3" for="op_venta"><i class="bi bi-cart me-1"></i> Para Venta</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-6 mb-2" id="container_precio_renta">
                            <label class="form-label small fw-semibold text-body">Tarifa de Renta (Por Día) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary text-secondary">$</span>
                                <input type="number" name="precio_dia" step="0.01" class="form-control form-control-sm bg-body text-body select-on-focus @error('precio_dia') is-invalid @enderror" 
                                       value="{{ old('precio_dia') }}" placeholder="0.00" min="0">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-2" id="container_precio_venta" style="display: none;">
                            <label class="form-label small fw-semibold text-body">Precio al Público (Venta) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary text-secondary">$</span>
                                <input type="number" name="precio_venta" step="0.01" class="form-control form-control-sm bg-body text-success fw-bold select-on-focus @error('precio_venta') is-invalid @enderror" 
                                       value="{{ old('precio_venta') }}" placeholder="0.00" min="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            
            <div class="card border-0 shadow-sm rounded-3 mb-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
                <div class="card-header bg-body-tertiary border-bottom py-2 px-3 rounded-top-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-box-seam me-2"></i>Clasificación y Stock</h6>
                </div>
                <div class="card-body p-3">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Categoría <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <select name="categoria_id" class="form-select bg-body text-body @error('categoria_id') is-invalid @enderror" required>
                                <option value="">Seleccione...</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria" title="Nueva Categoría">
                                <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline ms-1">Nueva</span>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Unidad de Medida <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <select name="unidad_medida_id" class="form-select bg-body text-body @error('unidad_medida_id') is-invalid @enderror" required>
                                <option value="">Seleccione...</option>
                                @foreach($unidades as $unidad)
                                    <option value="{{ $unidad->id }}" {{ old('unidad_medida_id') == $unidad->id ? 'selected' : '' }}>{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalUnidad" title="Nueva Unidad">
                                <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline ms-1">Nueva</span>
                            </button>
                        </div>
                    </div>

                    <hr class="my-2 text-secondary">

                    <div class="row g-2">
                        <div class="col-6 mb-2">
                            <label class="form-label small fw-semibold text-body">Stock Inicial <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control form-control-sm bg-body text-body select-on-focus @error('stock') is-invalid @enderror" 
                                   value="{{ old('stock') }}" placeholder="0" min="0" required>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label small fw-semibold text-danger">Alerta Mínima <span class="text-danger">*</span></label>
                            <input type="number" name="stock_minimo" class="form-control form-control-sm bg-body text-danger fw-bold select-on-focus @error('stock_minimo') is-invalid @enderror" 
                                   value="{{ old('stock_minimo') }}" placeholder="0" min="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 mb-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
                <div class="card-body p-3">
                    <label class="form-label small fw-semibold text-body">Fotografía del Producto</label>
                    <div id="dropzone" class="w-100 p-3 text-center border rounded-3 bg-body-tertiary" style="border: 2px dashed #0d6efd !important; cursor: pointer; transition: all 0.3s;">
                        <input type="file" name="imagen" id="imagen_input" class="d-none" accept="image/jpeg, image/png, image/jpg">
                        <div id="dropzone-text">
                            <i class="bi bi-camera text-primary mb-1" style="font-size: 1.8rem;"></i>
                            <h6 class="text-secondary small mb-0">Seleccionar o soltar imagen</h6>
                        </div>
                        <div id="image-preview-container" style="display: none; position: relative;">
                            <img id="image-preview" src="" alt="Vista previa" class="img-fluid rounded-3 shadow-sm" style="max-height: 140px;">
                            <button type="button" id="remove-image-btn" class="btn btn-danger btn-sm rounded-circle shadow" style="position: absolute; top: -10px; right: -10px;"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </div>
                    @error('imagen')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-check form-switch mb-3 ms-1">
                <input class="form-check-input" type="checkbox" name="activo" id="activo" {{ old('activo', true) ? 'checked' : '' }}>
                <label class="form-check-label ms-2 small fw-bold text-body" for="activo">Producto Activo y Visible</label>
            </div>

            <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm rounded-3"><i class="bi bi-save me-1"></i> Guardar Producto</button>
        </div>
    </div>
</form>

<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-tags me-2"></i>Nueva Categoría</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formCategoria" action="{{ route('categorias.store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Nombre de la categoría <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control form-control-sm bg-body text-body" placeholder="Ej: Herramientas Eléctricas" required autofocus>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-body">Descripción</label>
                        <textarea name="descripcion" class="form-control form-control-sm bg-body text-body" rows="2" placeholder="Opcional"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold" id="btnGuardarCategoria">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUnidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-rulers me-2"></i>Nueva Unidad de Medida</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formUnidad" action="{{ route('unidades.store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Nombre de la unidad <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control form-control-sm bg-body text-body" placeholder="Ej: Metro Lineal" required autofocus>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-body">Abreviatura <span class="text-danger">*</span></label>
                        <input type="text" name="abreviatura" class="form-control form-control-sm bg-body text-body" placeholder="Ej: m, kg, pza" required maxlength="10">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold" id="btnGuardarUnidad">Guardar Unidad</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selección de texto al enfocarse en números
    document.querySelectorAll('.select-on-focus').forEach(i => {
        i.addEventListener('focus', function() { this.select(); });
        i.addEventListener('click', function() { this.select(); });
    });

    // Control dinámico Renta/Venta
    const opRenta = document.getElementById('op_renta');
    const opVenta = document.getElementById('op_venta');
    const divRenta = document.getElementById('container_precio_renta');
    const divVenta = document.getElementById('container_precio_venta');

    function actualizarPrecios() {
        if (opRenta && divRenta) divRenta.style.display = opRenta.checked ? 'block' : 'none';
        if (opVenta && divVenta) divVenta.style.display = opVenta.checked ? 'block' : 'none';
    }
    if (opRenta) opRenta.addEventListener('change', actualizarPrecios);
    if (opVenta) opVenta.addEventListener('change', actualizarPrecios);
    actualizarPrecios();

    // Dropzone Fotografía
    const dropzone = document.getElementById('dropzone');
    const inputImagen = document.getElementById('imagen_input');
    const dropzoneText = document.getElementById('dropzone-text');
    const previewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const removeBtn = document.getElementById('remove-image-btn');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => dropzone.addEventListener(e, preventDef, false));
    function preventDef(e) { e.preventDefault(); e.stopPropagation(); }

    dropzone.addEventListener('drop', e => handleFiles(e.dataTransfer.files));
    dropzone.addEventListener('click', e => {
        if (e.target !== removeBtn && !removeBtn.contains(e.target)) inputImagen.click();
    });

    inputImagen.addEventListener('change', function() { handleFiles(this.files); });

    function handleFiles(files) {
        if (!files.length) return;
        const file = files[0];
        if (!file.type.startsWith('image/')) return alert('Selecciona una imagen válida.');
        
        const dt = new DataTransfer();
        dt.items.add(file);
        inputImagen.files = dt.files;

        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.src = e.target.result;
            dropzoneText.style.display = 'none';
            previewContainer.style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
    }

    removeBtn.addEventListener('click', e => {
        e.stopPropagation();
        inputImagen.value = '';
        imagePreview.src = '';
        previewContainer.style.display = 'none';
        dropzoneText.style.display = 'block';
    });

    // Peticiones AJAX Modales
    const setupAjaxForm = (formId, modalEl, btnId, selectName, formatText, toastMsg) => {
        const form = document.getElementById(formId);
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById(btnId);
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

            fetch(this.getAttribute('action'), {
                method: 'POST',
                body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(async r => {
                const data = await r.json();
                if (!r.ok) throw new Error(data.message || 'Error en el servidor');
                return data;
            })
            .then(data => {
                if (data.success) {
                    const select = document.querySelector(`select[name="${selectName}"]`);
                    if (select) {
                        const option = new Option(formatText(data), data.categoria?.id || data.unidad?.id, true, true);
                        select.add(option);
                    }
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    form.reset();
                    mostrarToast(toastMsg, 'success');
                }
            })
            .catch(err => mostrarToast(err.message, 'danger'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            });
        });
    };

    setupAjaxForm('formCategoria', document.getElementById('modalCategoria'), 'btnGuardarCategoria', 'categoria_id', d => d.categoria.nombre, 'Categoría creada exitosamente');
    setupAjaxForm('formUnidad', document.getElementById('modalUnidad'), 'btnGuardarUnidad', 'unidad_medida_id', d => d.unidad.abreviatura ? `${d.unidad.nombre} (${d.unidad.abreviatura})` : d.unidad.nombre, 'Unidad creada exitosamente');

    // Función Toasts
    function mostrarToast(msg, tipo = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        const toastId = 'toast-' + Date.now();
        const bg = tipo === 'success' ? 'bg-success text-white' : 'bg-danger text-white';
        const icon = tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        
        container.insertAdjacentHTML('beforeend', `
            <div id="${toastId}" class="toast align-items-center ${bg} border-0 shadow-lg" role="alert">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi ${icon} fs-5"></i>
                        <span>${msg}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        const el = document.getElementById(toastId);
        const t = new bootstrap.Toast(el, { delay: 3000 });
        t.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    // Validar check al enviar
    const formCrear = document.getElementById('formCrearProducto');
    if (formCrear) {
        formCrear.addEventListener('submit', function(e) {
            const rChecked = document.getElementById('op_renta')?.checked || false;
            const vChecked = document.getElementById('op_venta')?.checked || false;
            if (!rChecked && !vChecked) {
                e.preventDefault();
                mostrarToast('Debe seleccionar al menos un tipo de operación (Renta o Venta).', 'danger');
            }
        });
    }
});
</script>
@endsection