@extends('layouts.admin')

@section('content')
<style>
    /* Estética Adaptable Premium */
    .premium-tabs {
        border-bottom: 1px solid var(--bs-border-color);
        gap: 24px;
        margin-bottom: 24px;
    }
    .premium-tabs .nav-link {
        color: var(--bs-secondary-color);
        font-weight: 600;
        font-size: 14px;
        padding: 12px 4px;
        border: none;
        background: transparent;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        transition: all 0.2s ease;
    }
    .premium-tabs .nav-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        font-weight: 700;
    }
    .premium-tabs .nav-link:hover:not(.active) {
        color: var(--bs-body-color);
    }

    /* Paneles de Contenedor Adaptable */
    .panel-box {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.01);
        padding: 24px;
        margin-bottom: 24px;
    }
    .panel-title-area {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--bs-border-color);
        padding-bottom: 16px;
    }
    .panel-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--bs-heading-color);
        margin: 0;
    }

    /* Tarjetas de Sucursales */
    .branch-card {
        background: var(--bs-body-bg);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        margin-bottom: 12px;
        transition: border-color 0.2s ease;
    }
    .branch-card:hover {
        border-color: var(--bs-border-color-translucent);
    }
    .branch-meta {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .media-frame {
        width: 46px;
        height: 44px;
        border-radius: 50%;
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .media-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* Tabla Profesional de Usuarios */
    .user-table-wrapper {
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        overflow: hidden;
        background: var(--bs-body-bg);
    }
    .user-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .user-table th {
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--bs-border-color);
    }
    .user-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--bs-border-color);
        font-size: 13px;
        color: var(--bs-body-color);
        vertical-align: middle;
    }
    .user-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Estados y Badges */
    .badge-status {
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        display: inline-block;
    }
    .badge-status.active {
        background: #ecfdf5;
        color: #065f46;
    }
    .badge-status.inactive {
        background: #fef2f2;
        color: #991b1b;
    }
    
    /* Formulario */
    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--bs-body-color);
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 14px;
        border: 1px solid var(--bs-border-color);
        font-size: 13px;
        color: var(--bs-body-color);
        background-color: var(--bs-body-bg);
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.08);
    }
    .form-control:disabled, .form-select:disabled {
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        cursor: not-allowed;
    }

    /* Botones */
    .btn {
        font-size: 13px;
        font-weight: 600;
        padding: 9px 18px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .btn-primary {
        background: var(--bs-primary);
        border: none;
        color: white;
    }
    .btn-primary:hover {
        background: var(--bs-primary-dark, #0b5ed7);
    }
    .btn-action-outline {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        color: var(--bs-secondary-color);
        padding: 6px 12px;
        border-radius: 6px;
    }
    .btn-action-outline:hover {
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
    }

    /* 🔒 Estilos para elementos bloqueados */
    .locked-overlay {
        position: relative;
        opacity: 0.7;
        pointer-events: none;
    }
    .locked-badge { 
        position: absolute; 
        top: 8px; 
        right: 12px; 
        background: #fef2f2; 
        color: #991b1b; 
        font-size: 10px; 
        font-weight: 700; 
        padding: 4px 10px; 
        border-radius: 20px; 
        border: 1px solid #fecaca;
        z-index: 10;
        pointer-events: auto;
    }
</style>

<div class="container-fluid p-0 py-2">
    <!-- Header responsive -->
    <div class="page-header mb-4">
        <h3 class="fw-bold text-body mb-1"><i class="bi bi-shield-gear text-primary me-2"></i>Consola de Configuración Corporativa</h3>
        <p class="text-secondary small mb-0">Administra los datos globales de tu empresa, gestiona sucursales y controla los perfiles de acceso de tus empleados.</p>
    </div>

    {{-- ✅ Mensaje de alerta para Gerentes (informativo) --}}
    @if(auth()->user()->isGerente())
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Acceso de Gerente:</strong> Solo puedes modificar los datos de tu sucursal asignada. 
                    La gestión de empresa, usuarios y creación de sucursales está reservada al Administrador Global.
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <ul class="nav premium-tabs" id="configTabs" role="tablist">
        {{-- Solo Admin ve la pestaña de Empresa --}}
        @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <button class="nav-link active" id="empresa-tab" data-bs-toggle="tab" data-bs-target="#panel-empresa" type="button" role="tab">
                    <i class="bi bi-building me-2"></i>Datos de la Empresa
                </button>
            </li>
        @endif
        
        <li class="nav-item">
            <button class="nav-link {{ auth()->user()->isGerente() && !auth()->user()->isAdmin() ? 'active' : '' }}" id="sucursales-tab" data-bs-toggle="tab" data-bs-target="#panel-sucursales" type="button" role="tab">
                <i class="bi bi-geo-alt me-2"></i>Sucursales
            </button>
        </li>
        
        {{-- Solo Admin ve la pestaña de Usuarios --}}
        @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#panel-usuarios" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>Usuarios
                </button>
            </li>
        @endif
        
        <li class="nav-item">
            <button class="nav-link" id="plantillas-tab" data-bs-toggle="tab" data-bs-target="#panel-plantillas" type="button" role="tab">
                <i class="bi bi-file-earmark-richtext me-2"></i>Plantillas de Documentos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="configTabsContent">
        
        {{-- ============================================ --}}
        {{-- PANEL DE EMPRESA (SOLO ADMIN) --}}
        {{-- ============================================ --}}
        @if(auth()->user()->isAdmin())
        <div class="tab-pane fade show active" id="panel-empresa" role="tabpanel">
            <div class="panel-box rounded-4 shadow-sm">
                <h6 class="panel-title mb-4 fw-bold text-body">Información de la Entidad Legal y Marca</h6>
                
                <form action="{{ route('configuracion.empresa.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <div class="col-12 col-md-3 text-center border-end border-sm-0 pb-3 pb-md-0">
                            <label class="form-label d-block fw-semibold text-secondary mb-3">Logo General del Sistema</label>
                            <div class="mb-3 d-flex justify-content-center align-items-center bg-body-tertiary rounded-4 mx-auto" style="width: 150px; height: 160px; overflow: hidden; border: 2px dashed var(--bs-border-color);">
                                @if(\App\Helpers\ContentHelper::getCompanyData('empresa_logo'))
                                    <img id="logo-preview" src="{{ asset('storage/' . \App\Helpers\ContentHelper::getCompanyData('empresa_logo')) }}" class="img-fluid h-100 w-100 object-fit-cover" alt="Logo corporativo">
                                @else
                                    <img id="logo-preview" src="" class="img-fluid h-100 w-100 object-fit-cover d-none" alt="Vista previa del logo">
                                    <i id="logo-placeholder" class="bi bi-image text-secondary opacity-50 fs-1"></i>
                                @endif
                            </div>
                            <div class="input-group input-group-sm">
                                <input type="file" name="empresa_logo" id="empresa_logo" class="form-control bg-body text-body" accept="image/*">
                            </div>
                            <small class="text-secondary d-block mt-1" style="font-size: 11px;">Formatos recomendados: PNG, JPG (Max 2MB)</small>
                        </div>

                        <div class="col-12 col-md-9">
                            <div class="row g-2">
                                <div class="col-12 col-md-7">
                                    <label class="form-label small fw-semibold text-body">Nombre de la Empresa o Razón Social <span class="text-danger">*</span></label>
                                    <input type="text" name="empresa_nombre" class="form-control form-control-sm bg-body text-body" placeholder="Ej. Corporativo Viramontes S.A." value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_nombre') }}" required>
                                </div>
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-semibold text-body">RFC / Identificación Fiscal</label>
                                    <input type="text" name="empresa_rfc" class="form-control form-control-sm bg-body text-body" placeholder="Ej. ABC123456XYZ" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_rfc') }}">
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold text-body">Dirección Fiscal / Matriz</label>
                                    <input type="text" name="empresa_direccion" class="form-control form-control-sm bg-body text-body" placeholder="Calle, Número, Colonia, C.P." value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_direccion') }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-body">Teléfono Corporativo</label>
                                    <input type="text" name="empresa_telefono" class="form-control form-control-sm bg-body text-body" placeholder="Ej. (618) 123-4567" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_telefono') }}">
                                </div>
                            </div>

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold rounded-3 shadow-sm">
                                    <i class="bi bi-cloud-check me-2"></i>Guardar Configuración Corporativa
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        
        <div class="tab-pane fade show active" id="panel-sucursales" role="tabpanel">
            <!-- Usamos bg-body-tertiary para dar un fondo adaptable con buena profundidad -->
            <div class="panel-box bg-body-tertiary p-4 rounded-4 shadow-sm">
                <div class="panel-title-area d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <!-- text-body se adaptará automáticamente a blanco o negro -->
                    <h5 class="panel-title fw-bold text-body m-0">Unidades de Negocio y Sucursales</h5>
                    {{-- Solo Admin puede crear sucursales --}}
                    @if(auth()->user()->isAdmin())
                        <button class="btn btn-primary shadow-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearSucursal">
                            <i class="bi bi-plus-lg me-1"></i> Registrar Nueva Sucursal
                        </button>
                    @endif
                </div>

                <div class="branch-container">
                    @forelse($sucursales as $suc)
                        <div class="branch-card d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between p-3 border rounded-3 mb-2 gap-3 shadow-none">
                            <div class="branch-meta d-flex align-items-center gap-3 w-100 text-truncate">
                                <div class="media-frame rounded-circle bg-body-tertiary border d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 45px; height: 44px;">
                                    @if($suc->logo)
                                        <img src="{{ asset('storage/' . $suc->logo) }}" class="w-100 h-100 object-fit-cover" alt="Logo sucursal">
                                    @else
                                        <i class="bi bi-geo-alt text-secondary opacity-50 fs-5"></i>
                                    @endif
                                </div>
                                <div class="text-truncate">
                                    <h6 class="branch-name fw-bold mb-1 text-body small text-truncate">{{ $suc->nombre }}</h6>
                                    <p class="branch-desc text-secondary mb-0 d-flex flex-wrap gap-2 gap-sm-3" style="font-size: 11px;">
                                        <span><i class="bi bi-pin-map text-primary me-1"></i>{{ $suc->direccion }}</span>
                                        @if($suc->telefono)
                                            <span><i class="bi bi-telephone text-success me-1"></i>{{ $suc->telefono }}</span>
                                        @endif
                                        @if($suc->rfc)
                                            <span><i class="bi bi-hash text-secondary me-1"></i>{{ $suc->rfc }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-auto ms-sm-0 flex-shrink-0">
                                <span class="badge rounded-pill px-2.5 py-1.5 {{ $suc->activa ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}" style="font-size: 10.5px; font-weight: 600;">
                                    {{ $suc->activa ? 'Operativa' : 'Suspendida' }}
                                </span>
                                <button class="btn btn-action-outline btn-sm border rounded-3 fw-semibold p-1 px-2 text-secondary" data-bs-toggle="modal" data-bs-target="#modalEditarSucursal{{ $suc->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Editar Sucursal (Versión Admin vs Gerente) --}}
                        <div class="modal fade" id="modalEditarSucursal{{ $suc->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
                                    <div class="modal-header bg-dark text-white border-0 py-2">
                                        <h5 class="modal-title fw-bold"><i class="bi bi-building-gear me-2"></i>Actualizar Sucursal</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('configuracion.sucursal.update', $suc->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <div class="modal-body p-3 bg-body-tertiary">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-body">Nombre de la Sucursal <span class="text-danger">*</span></label>
                                                <input type="text" name="nombre" class="form-control form-control-sm bg-body text-body" value="{{ $suc->nombre }}" required>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-body">RFC de Facturación</label>
                                                    <input type="text" name="rfc" class="form-control form-control-sm bg-body text-body" value="{{ $suc->rfc }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-body">Teléfono de Atención</label>
                                                    <input type="text" name="telefono" class="form-control form-control-sm bg-body text-body" value="{{ $suc->telefono }}">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-body">Dirección Completa <span class="text-danger">*</span></label>
                                                <input type="text" name="direccion" class="form-control form-control-sm bg-body text-body" value="{{ $suc->direccion }}" required>
                                            </div>
                                            <div class="row g-2 align-items-center">
                                                <div class="{{ auth()->user()->isAdmin() ? 'col-8' : 'col-12' }}">
                                                    <label class="form-label fw-semibold small text-muted">Cambiar Imagen/Logo</label>
                                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                                </div>
                                                {{-- Solo Admin puede cambiar estado operativo --}}
                                                @if(auth()->user()->isAdmin())
                                                    <div class="col-4">
                                                        <label class="form-label fw-semibold small text-muted">Estado</label>
                                                        <select name="activa" class="form-select">
                                                            <option value="1" {{ $suc->activa ? 'selected' : '' }}>Operativa</option>
                                                            <option value="0" {{ !$suc->activa ? 'selected' : '' }}>Suspendida</option>
                                                        </select>
                                                    </div>
                                                @else
                                                    {{-- Campo oculto para mantener el estado actual --}}
                                                    <input type="hidden" name="activa" value="{{ $suc->activa }}">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer py-2 bg-body">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 border border-dashed rounded-4 bg-body-tertiary">
                            <div class="bg-body border d-inline-flex p-3 rounded-circle mb-3 text-secondary">
                                <i class="bi bi-geo fs-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark">No hay sucursales registradas</h5>
                            <p class="text-muted small mx-auto" style="max-width: 360px;">Registra tu primera sucursal física o almacén para poder enlazar inventarios y cajeros operadores.</p>
                            @if(auth()->user()->isAdmin())
                                <button class="btn btn-sm btn-primary fw-bold px-3 rounded-3 mt-2" data-bs-toggle="modal" data-bs-target="#modalCrearSucursal">
                                    <i class="bi bi-plus-lg me-1"></i> Configurar ahora
                                </button>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PANEL DE USUARIOS (SOLO ADMIN) --}}
        {{-- ============================================ --}}
        @if(auth()->user()->isAdmin())
        <div class="tab-pane fade" id="panel-usuarios" role="tabpanel">
            <div class="panel-box rounded-4 shadow-sm">
                <div class="panel-title-area d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h6 class="panel-title fw-bold text-body m-0">Usuarios Registrados </h6>
                    <button class="btn btn-dark btn-sm shadow-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                        <i class="bi bi-person-plus-fill me-1"></i> Registrar Empleado
                    </button>
                </div>

                <div class="user-table-wrapper shadow-none">
                    <div class="table-responsive">
                        <table class="user-table table mb-0 align-middle" style="font-size: 13px;">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="ps-3 py-2 text-secondary">Nombre </th>
                                    <th class="text-secondary">Email Corporativo</th>
                                    <th class="text-secondary">Sucursal Asignada</th>
                                    <th class="text-secondary">Rol Sistema</th>
                                    <th class="text-secondary">Estado</th>
                                    <th class="text-end pe-3 text-secondary">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuarios as $user)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center border overflow-hidden" style="width: 36px; height: 36px; font-size: 13px;">
                                                @if($user->foto)
                                                    <img src="{{ asset('storage/' . $user->foto) }}" class="w-100 h-100 object-fit-cover" alt="Avatar">
                                                @else
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="text-truncate">
                                                <span class="fw-bold text-body d-block text-truncate small" style="max-width: 140px;">{{ $user->name }}</span>
                                                <small class="text-secondary" style="font-size: 10px;">ID: #{{ $user->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-secondary text-truncate small" style="max-width: 160px;">{{ $user->email }}</td>
                                    <td>
                                        <span class="fw-semibold text-body small">
                                            <i class="bi bi-geo-alt text-secondary me-1"></i>{{ $user->sucursal->nombre ?? 'Sin asignar' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->role == 'admin')
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 font-sans text-uppercase" style="font-size: 10px; font-weight: 600;">Administrador</span>
                                        @elseif($user->role == 'gerente')
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1 font-sans text-uppercase" style="font-size: 10px; font-weight: 600;">Gerente</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 font-sans text-uppercase" style="font-size: 10px; font-weight: 600;">Cajero / POS</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $user->status == 'activo' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} rounded-pill px-2 py-1 fw-semibold" style="font-size: 10.5px;">
                                            {{ $user->status == 'activo' ? 'Activo' : 'Inhabilitado' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group shadow-none border rounded-3 bg-body overflow-hidden">
                                            <button class="btn btn-light btn-sm border-0 bg-transparent" title="Cambiar Contraseña" data-bs-toggle="modal" data-bs-target="#modalPassword{{ $user->id }}">
                                                <i class="bi bi-key-fill text-secondary"></i>
                                            </button>
                                            <button class="btn btn-light btn-sm border-0 border-start bg-transparent" title="Editar Operador" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario{{ $user->id }}">
                                                <i class="bi bi-pencil-square text-secondary"></i>
                                            </button>
                                            
                                            @if($user->status == 'activo')
                                                <form action="{{ route('configuracion.usuarios.baja', $user->id) }}" method="POST" class="d-inline m-0">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-light btn-sm border-0 border-start bg-transparent text-danger" title="Dar de Baja" onclick="return confirm('¿Suspender accesos al sistema para este operador?')">
                                                        <i class="bi bi-person-x-fill"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('configuracion.usuarios.alta', $user->id) }}" method="POST" class="d-inline m-0">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-light btn-sm border-0 border-start bg-transparent text-success" title="Reactivar Operador">
                                                        <i class="bi bi-person-check-fill"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                @include('configuracion.partials.modales_usuario', ['user' => $user])

                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 bg-light">
                                    <div class="text-muted mb-2"><i class="bi bi-people fs-2 text-secondary"></i></div>
                                    <h6 class="fw-bold text-dark mb-1">No hay operadores secundarios dados de alta</h6>
                                    <p class="text-muted small mb-0">Registra nuevos usuarios para gestionar el sistema.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- PANEL DE PLANTILLAS (ADMIN Y GERENTE) --}}
        {{-- ============================================ --}}
        <div class="tab-pane fade" id="panel-plantillas" role="tabpanel">
            <div class="panel-box rounded-4 shadow-sm">
                <div class="panel-title-area mb-4 pb-2 border-bottom">
                    <h6 class="panel-title fw-bold text-body m-0">Estructura Legal de Documentos Imprimibles</h6>
                </div>
                
                <div class="row g-3">
                    @forelse($plantillas as $p)
                    <div class="col-12 col-xl-6">
                        <div class="card border p-3 p-md-4 rounded-4 bg-body-tertiary h-100" style="border-color: var(--bs-border-color) !important;">
                            <form action="{{ route('configuracion.plantilla.update', $p->id) }}" method="POST" class="d-flex flex-column h-100 m-0">
                                @csrf @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-primary text-uppercase tracking-wider" style="font-size: 11px;">
                                        <i class="bi bi-file-earmark-text-fill me-1"></i> Identificador: {{ str_replace('_', ' ', $p->tipo) }}
                                    </label>
                                    <input type="text" name="titulo" class="form-control form-control-sm bg-body text-body fw-semibold" value="{{ $p->titulo }}" placeholder="Título oficial del documento">
                                </div>
                                <div class="mb-3 flex-grow-1">
                                    <label class="form-label text-secondary small fw-semibold mb-1">Cuerpo del Documento / Cláusulas Legales</label>
                                    <textarea name="contenido" class="form-control font-monospace small bg-body text-body" rows="9" style="font-size: 12px; line-height: 1.5; resize: vertical;" required>{{ $p->contenido }}</textarea>
                                </div>
                                
                                <div class="p-3 bg-body border rounded-3 mb-3">
                                    <span class="d-block fw-bold text-body mb-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="bi bi-code-slash text-secondary me-1"></i> Atajos de Variables Rápidas:
                                    </span>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="insertVariable(this, '{cliente}')">{cliente}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="insertVariable(this, '{folio}')">{folio}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="insertVariable(this, '{monto_total}')">{monto_total}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="insertVariable(this, '{monto_neto}')">{monto_neto}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="insertVariable(this, '{deposito}')">{deposito}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="insertVariable(this, '{fecha_fin}')">{fecha_fin}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="insertVariable(this, '{empresa}')">{empresa}</button>
                                    </div>
                                    <small class="text-secondary d-block mt-2" style="font-size: 11px; line-height: 1.3;"><i class="bi bi-info-circle me-1"></i> Haz clic en los botones superiores para insertar variables automáticamente al texto.</small>
                                </div>
                                
                                <button type="submit" class="btn btn-dark btn-sm w-100 py-2 fw-bold shadow-sm mt-auto">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Guardar Cambios de Formato
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5 border border-dashed rounded-4 bg-body-tertiary">
                        <i class="bi bi-file-earmark-richtext text-secondary fs-1 mb-2"></i>
                        <h6 class="fw-bold text-body">No hay plantillas base inicializadas</h6>
                        <p class="text-secondary small mb-0">Ejecuta el seeder <code>PlantillasDocumentosSeeder</code> desde tu terminal para cargar los esquemas iniciales.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL CREAR SUCURSAL (SOLO ADMIN) --}}
{{-- ============================================ --}}
@if(auth()->user()->isAdmin())
<div class="modal fade" id="modalCrearSucursal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-dark text-white border-0 py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-building-add me-2"></i>Registrar Nueva Unidad de Negocio</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('configuracion.sucursal.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3 bg-body-tertiary">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Nombre Comercial de la Sucursal <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control form-control-sm bg-body text-body" required placeholder="Ej: Sucursal Norte Viramontes">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body">RFC Sucursal</label>
                            <input type="text" name="rfc" class="form-control form-control-sm bg-body text-body" placeholder="Opcional">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body">Teléfono Comercial</label>
                            <input type="text" name="telefono" class="form-control form-control-sm bg-body text-body" placeholder="Para tickets">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Dirección Geográfica Completa <span class="text-danger">*</span></label>
                        <input type="text" name="direccion" class="form-control form-control-sm bg-body text-body" required placeholder="Calle, Número, Colonia, C.P.">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-body">Logo de la Sucursal</label>
                        <input type="file" name="logo" class="form-control form-control-sm bg-body text-body" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer py-2 bg-body">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">Registrar Sucursal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ============================================ --}}
{{-- MODAL CREAR USUARIO (SOLO ADMIN) --}}
{{-- ============================================ --}}
@if(auth()->user()->isAdmin())
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-dark text-white border-0 py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Registro de Usuario</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('configuracion.usuarios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3 bg-body-tertiary">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Nombre Completo del Empleado <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm bg-body text-body" required placeholder="Nombre del empleado">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Correo Electrónico Corp. <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm bg-body text-body" required placeholder="empleado@correo.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Contraseña Base <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control form-control-sm bg-body text-body" required minlength="6" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body">Rol Autorizado <span class="text-danger">*</span></label>
                            <select name="role" class="form-select form-select-sm bg-body text-body" required>
                                <option value="cajero" selected>Cajero</option>
                                <option value="gerente">Gerente de Sucursal</option>
                                <option value="admin">Administrador Global</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body">Vincular a Sucursal <span class="text-danger">*</span></label>
                            <select name="sucursal_id" class="form-select form-select-sm bg-body text-body" required>
                                <option value="" disabled selected>Seleccione...</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small text-muted">Fotografía de Perfil (Opcional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
    // ✅ Función corregida para insertar variables en textarea
    function insertVariable(button, variable) {
        const form = button.closest('form');
        const textarea = form.querySelector('textarea');
        if (textarea) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + ' ' + variable + ' ' + text.substring(end);
            textarea.focus();
            textarea.selectionStart = start + variable.length + 2;
            textarea.selectionEnd = start + variable.length + 2;
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        let activeTab = "{{ session('tab') }}";
        
        if (!activeTab) {
            activeTab = localStorage.getItem('activeConfigTab');
        }

        if (activeTab) {
            let tabTrigger = document.querySelector(`#${activeTab}-tab`);
            if (tabTrigger) {
                document.querySelectorAll('.premium-tabs .nav-link').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content .tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
                
                tabTrigger.classList.add('active');
                let targetPane = document.querySelector(tabTrigger.getAttribute('data-bs-target'));
                if (targetPane) targetPane.classList.add('show', 'active');
            }
        }

        document.querySelectorAll('#configTabs button').forEach(button => {
            button.addEventListener('shown.bs.tab', function (e) {
                let id = e.target.id.replace('-tab', '');
                localStorage.setItem('activeConfigTab', id);
            });
        });
    });

    // Preview de imagen para logo de empresa
    document.getElementById('empresa_logo')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('logo-preview');
                let placeholder = document.getElementById('logo-placeholder');
                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                if (placeholder) placeholder.classList.add('d-none');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
@endsection