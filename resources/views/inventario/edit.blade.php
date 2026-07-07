@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <h2 class="mb-0">Editar Ficha del Producto</h2>
    <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Regresar</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('inventario.update', $equipo) }}" method="POST" enctype="multipart/form-data" id="formEditarProducto">
    @method('PUT')
    @csrf
    
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <h5 class="text-primary"><i class="bi bi-upc-scan me-2"></i>Identificación del Producto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Nombre del equipo / producto *</label>
                            <input type="text" name="nombre" class="form-control form-control-lg @error('nombre') is-invalid @enderror" 
                                   value="{{ old('nombre', $equipo->nombre) }}" required>
                        </div>
                        
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label text-muted">Código Interno</label>
                            <input type="text" class="form-control bg-light" value="{{ $equipo->codigo }}" disabled>
                            <input type="hidden" name="codigo" value="{{ $equipo->codigo }}">
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Código de Barras (Escáner)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-upc"></i></span>
                                <input type="text" name="codigo_barras" class="form-control font-monospace @error('codigo_barras') is-invalid @enderror" 
                                       value="{{ old('codigo_barras', $equipo->codigo_barras) }}" placeholder="Clic aquí y usa el escáner">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Descripción detallada</label>
                            <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $equipo->descripcion) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <h5 class="text-primary"><i class="bi bi-cash-coin me-2"></i>Operación y Precios</h5>
                </div>
                <div class="card-body">
                    @php
                        $oldOps = old('operaciones');
                        $isRenta = $oldOps ? in_array('renta', $oldOps) : in_array($equipo->tipo_operacion, ['renta', 'ambas']);
                        $isVenta = $oldOps ? in_array('venta', $oldOps) : in_array($equipo->tipo_operacion, ['venta', 'ambas']);
                    @endphp

                    <div class="mb-4">
                        <label class="form-label fw-bold d-block">Tipo de Disponibilidad *</label>
                        <div class="d-flex flex-wrap gap-2">
                            <input type="checkbox" class="btn-check" name="operaciones[]" id="op_renta" value="renta" {{ $isRenta ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="op_renta"><i class="bi bi-clock-history me-1"></i> Para Renta</label>

                            <input type="checkbox" class="btn-check" name="operaciones[]" id="op_venta" value="venta" {{ $isVenta ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="op_venta"><i class="bi bi-cart me-1"></i> Para Venta</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3" id="container_precio_renta" style="{{ $isRenta ? '' : 'display: none;' }}">
                            <label class="form-label fw-bold">Tarifa de Renta (Por Día) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" name="precio_dia" step="0.01" class="form-control form-control-lg select-on-focus @error('precio_dia') is-invalid @enderror" 
                                       value="{{ old('precio_dia', $equipo->precio_dia) }}" placeholder="0.00" min="0">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3" id="container_precio_venta" style="{{ $isVenta ? '' : 'display: none;' }}">
                            <label class="form-label fw-bold">Precio al Público (Venta) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" name="precio_venta" step="0.01" class="form-control form-control-lg select-on-focus text-success @error('precio_venta') is-invalid @enderror" 
                                       value="{{ old('precio_venta', $equipo->precio_venta) }}" placeholder="0.00" min="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <h5 class="text-primary"><i class="bi bi-box-seam me-2"></i>Clasificación y Stock</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Categoría *</label>
                        <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id', $equipo->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Unidad de Medida *</label>
                        <select name="unidad_medida_id" class="form-select @error('unidad_medida_id') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            @foreach($unidades as $unidad)
                                <option value="{{ $unidad->id }}" {{ old('unidad_medida_id', $equipo->unidad_medida_id) == $unidad->id ? 'selected' : '' }}>
                                    {{ $unidad->nombre }} ({{ $unidad->abreviatura }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-dark">Stock Actual *</label>
                            <input type="number" name="stock" class="form-control form-control-lg select-on-focus @error('stock') is-invalid @enderror" 
                                   value="{{ old('stock', $equipo->stock) }}" placeholder="0" min="0" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-danger">Alerta Mínima *</label>
                            <input type="number" name="stock_minimo" class="form-control form-control-lg select-on-focus @error('stock_minimo') is-invalid @enderror" 
                                   value="{{ old('stock_minimo', $equipo->stock_minimo) }}" placeholder="0" min="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <label class="form-label fw-bold">Fotografía del Producto</label>
                    <input type="hidden" name="quitar_imagen" id="quitar_imagen" value="0">
                    
                    <div id="dropzone" class="w-100 p-4 text-center border rounded bg-light" style="border: 2px dashed #0d6efd !important; cursor: pointer; transition: all 0.3s;">
                        <input type="file" name="imagen" id="imagen_input" class="d-none" accept="image/jpeg, image/png, image/jpg">
                        
                        <div id="dropzone-text" style="{{ $equipo->imagen ? 'display: none;' : 'display: block;' }}">
                            <i class="bi bi-camera text-primary mb-2" style="font-size: 2rem;"></i>
                            <h6 class="text-secondary mb-0">Seleccionar o soltar nueva imagen</h6>
                        </div>

                        <div id="image-preview-container" style="{{ $equipo->imagen ? 'display: inline-block;' : 'display: none;' }} position: relative;">
                            <img id="image-preview" src="{{ $equipo->imagen ? Storage::url($equipo->imagen) : '' }}" alt="Vista previa" class="img-fluid rounded shadow-sm" style="max-height: 150px;">
                            <button type="button" id="remove-image-btn" class="btn btn-danger btn-sm rounded-circle shadow" style="position: absolute; top: -10px; right: -10px;"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </div>
                    @error('imagen')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-check form-switch mb-4 ms-1">
                <input class="form-check-input" type="checkbox" name="activo" id="activo" {{ old('activo', $equipo->activo) ? 'checked' : '' }} style="transform: scale(1.3);">
                <label class="form-check-label ms-2 fw-bold" for="activo">Producto Activo y Visible</label>
            </div>

            <button type="submit" class="btn btn-warning btn-lg w-100 shadow-sm"><i class="bi bi-pencil-square me-2"></i> Actualizar Producto</button>
        </div>
    </div>
</form>

<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white bg-opacity-25 rounded p-2">
                        <i class="bi bi-tags fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold" id="modalCategoriaLabel">Nueva Categoría</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <form id="formCategoria" action="{{ route('categorias.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-4">
                    <div class="alert alert-light border mb-4 d-flex align-items-center gap-2 py-2" role="alert">
                        <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                        <small class="text-muted">Complete los datos para registrar una nueva categoría de productos.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-bookmark-fill text-primary me-1"></i> Nombre de la categoría <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-tag text-primary"></i>
                            </span>
                            <input type="text" name="nombre" class="form-control border-start-0 ps-0" 
                                   placeholder="Ej: Herramientas Eléctricas" required autofocus>
                        </div>
                        <small class="text-muted">Ingrese un nombre descriptivo y único para la categoría.</small>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-card-text text-primary me-1"></i> Descripción
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 align-items-start pt-2">
                                <i class="bi bi-text-paragraph text-primary"></i>
                            </span>
                            <textarea name="descripcion" class="form-control border-start-0 ps-0" rows="3" 
                                      placeholder="Breve descripción de esta categoría (opcional)"></textarea>
                        </div>
                        <small class="text-muted">Opcional. Ayuda a identificar el propósito de esta categoría.</small>
                    </div>
                </div>
                
                <div class="modal-footer border-0 bg-light rounded-bottom px-4 py-3">
                    <button type="button" class="btn btn-light border shadow-sm px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4" id="btnGuardarCategoria">
                        <i class="bi bi-check2-circle me-1"></i> Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUnidad" tabindex="-1" aria-labelledby="modalUnidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white bg-opacity-25 rounded p-2">
                        <i class="bi bi-rulers fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold" id="modalUnidadLabel">Nueva Unidad de Medida</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <form id="formUnidad" action="{{ route('unidades.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-4">
                    <div class="alert alert-light border mb-4 d-flex align-items-center gap-2 py-2" role="alert">
                        <i class="bi bi-info-circle-fill text-success fs-5"></i>
                        <small class="text-muted">Defina una nueva unidad de medida para sus productos.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-box-fill text-success me-1"></i> Nombre de la unidad <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-rulers text-success"></i>
                            </span>
                            <input type="text" name="nombre" class="form-control border-start-0 ps-0" 
                                   placeholder="Ej: Metro Lineal" required autofocus>
                        </div>
                        <small class="text-muted">Nombre completo de la unidad de medida.</small>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-type-bold text-success me-1"></i> Abreviatura <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-pencil-square text-success"></i>
                            </span>
                            <input type="text" name="abreviatura" class="form-control border-start-0 ps-0" 
                                   placeholder="Ej: m, kg, pza, lt" required maxlength="10">
                        </div>
                        <small class="text-muted">Abreviatura corta (máx. 10 caracteres). Ej: kg, m, pza, lt.</small>
                    </div>
                </div>
                
                <div class="modal-footer border-0 bg-light rounded-bottom px-4 py-3">
                    <button type="button" class="btn btn-light border shadow-sm px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success shadow-sm px-4" id="btnGuardarUnidad">
                        <i class="bi bi-check2-circle me-1"></i> Guardar Unidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.select-on-focus').forEach(function(input) {
        input.addEventListener('focus', function() {
            this.select();
        });
        input.addEventListener('click', function() {
            this.select();
        });
    });

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

    const dropzone = document.getElementById('dropzone');
    const inputImagen = document.getElementById('imagen_input');
    const dropzoneText = document.getElementById('dropzone-text');
    const previewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const removeBtn = document.getElementById('remove-image-btn');
    const inputQuitarImagen = document.getElementById('quitar_imagen');
    let backupFile = null; 

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => dropzone.addEventListener(eventName, preventDefaults, false));
    function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }

    ['dragenter', 'dragover'].forEach(eventName => dropzone.addEventListener(eventName, () => { dropzone.classList.remove('bg-light'); dropzone.classList.add('bg-white'); }, false));
    ['dragleave', 'drop'].forEach(eventName => dropzone.addEventListener(eventName, () => { dropzone.classList.remove('bg-white'); dropzone.classList.add('bg-light'); }, false));

    dropzone.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files));
    dropzone.addEventListener('click', (e) => { if (e.target !== removeBtn && !removeBtn.contains(e.target)) inputImagen.click(); });

    inputImagen.addEventListener('change', function(e) {
        if (this.files.length > 0) handleFiles(this.files);
        else if (backupFile) { const dt = new DataTransfer(); dt.items.add(backupFile); this.files = dt.files; }
    });

    function handleFiles(files) {
        if (files.length === 0) return;
        const file = files[0];
        if (!file.type.startsWith('image/')) { alert('Selecciona una imagen válida.'); return; }
        backupFile = file; 
        const dt = new DataTransfer(); dt.items.add(file); inputImagen.files = dt.files;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.src = e.target.result;
            dropzoneText.style.display = 'none';
            previewContainer.style.display = 'inline-block';
            inputQuitarImagen.value = '0'; 
        }
        reader.readAsDataURL(file);
    }

    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation(); 
        inputImagen.value = ''; backupFile = null; imagePreview.src = '';
        previewContainer.style.display = 'none'; dropzoneText.style.display = 'block';
        inputQuitarImagen.value = '1'; 
    });

    const formCategoria = document.getElementById('formCategoria');
    const modalCategoriaEl = document.getElementById('modalCategoria');
    const modalCategoria = modalCategoriaEl ? new bootstrap.Modal(modalCategoriaEl) : null;

    if (formCategoria) {
        formCategoria.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnGuardar = document.getElementById('btnGuardarCategoria');
            const btnOriginalHTML = btnGuardar.innerHTML;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
            
            const formData = new FormData(this);
            
            fetch(this.getAttribute('action'), {
                method: 'POST', 
                body: formData, 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async r => {
                const data = await r.json();
                if (!r.ok) throw new Error(data.message || 'Error en el servidor');
                return data;
            })
            .then(data => {
                if (data.success) {
                    const select = document.querySelector('select[name="categoria_id"]');
                    if(select) {
                        const option = new Option(data.categoria.nombre, data.categoria.id, true, true);
                        select.add(option);
                    }
                    if (modalCategoria) modalCategoria.hide();
                    formCategoria.reset();
                    mostrarToast('Categoría creada exitosamente', 'success');
                } else {
                    throw new Error(data.message || 'Error al guardar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarToast(error.message || 'Ocurrió un error al guardar la categoría', 'danger');
            })
            .finally(() => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = btnOriginalHTML;
            });
        });
    }

    const formUnidad = document.getElementById('formUnidad');
    const modalUnidadEl = document.getElementById('modalUnidad');
    const modalUnidad = modalUnidadEl ? new bootstrap.Modal(modalUnidadEl) : null;

    if (formUnidad) {
        formUnidad.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnGuardar = document.getElementById('btnGuardarUnidad');
            const btnOriginalHTML = btnGuardar.innerHTML;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
            
            const formData = new FormData(this);
            
            fetch(this.getAttribute('action'), {
                method: 'POST', 
                body: formData, 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async r => {
                const data = await r.json();
                if (!r.ok) throw new Error(data.message || 'Error en el servidor');
                return data;
            })
            .then(data => {
                if (data.success) {
                    const select = document.querySelector('select[name="unidad_medida_id"]');
                    if(select) {
                        const texto = data.unidad.abreviatura 
                            ? `${data.unidad.nombre} (${data.unidad.abreviatura})` 
                            : data.unidad.nombre;
                        const option = new Option(texto, data.unidad.id, true, true);
                        select.add(option);
                    }
                    if (modalUnidad) modalUnidad.hide();
                    formUnidad.reset();
                    mostrarToast('Unidad de medida creada exitosamente', 'success');
                } else {
                    throw new Error(data.message || 'Error al guardar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarToast(error.message || 'Ocurrió un error al guardar la unidad', 'danger');
            })
            .finally(() => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = btnOriginalHTML;
            });
        });
    }

    function mostrarToast(mensaje, tipo = 'success') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const bgClass = tipo === 'success' ? 'bg-success text-white' : 'bg-danger text-white';
        const iconClass = tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi ${iconClass} fs-5"></i>
                        <span>${mensaje}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    const formEditar = document.getElementById('formEditarProducto');
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            const rentaChecked = document.getElementById('op_renta')?.checked || false;
            const ventaChecked = document.getElementById('op_venta')?.checked || false;
            
            if (!rentaChecked && !ventaChecked) {
                e.preventDefault();
                mostrarToast('Debe seleccionar al menos un tipo de operación (Renta o Venta).', 'danger');
            }
        });
    }

    if (modalCategoriaEl) {
        modalCategoriaEl.addEventListener('hidden.bs.modal', function() {
            formCategoria.reset();
        });
    }
    
    if (modalUnidadEl) {
        modalUnidadEl.addEventListener('hidden.bs.modal', function() {
            formUnidad.reset();
        });
    }
});
</script>
@endsection