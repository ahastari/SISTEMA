@extends('layouts.admin')

@section('content')
<style>
    /* Estética Adaptable Premium y Unificada */
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }
    
    .premium-tabs {
        border-bottom: 1px solid var(--bs-border-color);
        gap: 8px;
        margin-bottom: 24px;
    }
    .premium-tabs .nav-link {
        color: var(--bs-secondary-color);
        font-weight: 600;
        font-size: 14px;
        padding: 12px 20px;
        border: none;
        background: transparent;
        border-bottom: 3px solid transparent;
        border-radius: 6px 6px 0 0;
        transition: all 0.2s ease;
    }
    .premium-tabs .nav-link:hover:not(.active) {
        color: var(--bs-body-color);
        background: var(--bs-tertiary-bg);
    }
    .premium-tabs .nav-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: var(--bs-primary-bg-subtle);
        font-weight: 700;
    }

    /* Paneles estilo Tarjeta (Consistente con Nuevo Cliente) */
    .form-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color) !important;
        border-radius: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .section-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--bs-primary);
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--bs-border-color);
    }

    /* Tarjetas de Sucursales */
    .branch-card {
        background: var(--bs-body-bg);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .branch-card:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .media-frame {
        width: 48px;
        height: 48px;
        border-radius: 10px;
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
        border-radius: 12px;
        overflow: hidden;
        background: var(--bs-body-bg);
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
        vertical-align: middle;
    }
    .user-table tr:last-child td { border-bottom: none; }
</style>

<div class="container-fluid p-0 py-2">
    <!-- Header responsive -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="page-title mb-1">
                <i class="bi bi-shield-gear text-primary me-2"></i>Consola de Configuración
            </h2>
            <p class="text-secondary small mb-0">Administra los datos globales, sucursales y perfiles de acceso.</p>
        </div>
    </div>

    {{-- Alertas del Sistema --}}
    @if(auth()->user()->isGerente() && !auth()->user()->isAdmin())
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Acceso de Gerente:</strong> Solo puedes modificar los datos de tu sucursal asignada. 
                    La gestión de empresa, usuarios y creación de sucursales está reservada al Administrador.
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-x-octagon-fill me-2 fs-5"></i>
                <div>
                    <strong>No se pudieron guardar los cambios:</strong>
                    <ul class="mb-0 mt-1 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- TABS NAVEGACIÓN -->
    <ul class="nav premium-tabs" id="configTabs" role="tablist">
        @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <button class="nav-link active" id="empresa-tab" data-bs-toggle="tab" data-bs-target="#panel-empresa" type="button" role="tab">
                    <i class="bi bi-building me-2"></i>Empresa
                </button>
            </li>
        @endif
        
        <li class="nav-item">
            <button class="nav-link {{ (auth()->user()->isGerente() && !auth()->user()->isAdmin()) ? 'active' : '' }}" id="sucursales-tab" data-bs-toggle="tab" data-bs-target="#panel-sucursales" type="button" role="tab">
                <i class="bi bi-geo-alt me-2"></i>Sucursales
            </button>
        </li>
        
        @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#panel-usuarios" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>Usuarios
                </button>
            </li>
        @endif
        
        @if(auth()->user()->isAdmin() || auth()->user()->isGerente())
            <li class="nav-item">
                <button class="nav-link" id="plantillas-tab" data-bs-toggle="tab" data-bs-target="#panel-plantillas" type="button" role="tab">
                    <i class="bi bi-file-earmark-richtext me-2"></i>Plantillas de Documentos
                </button>
            </li>
        @endif
    </ul>

    <!-- CONTENIDO TABS -->
    <div class="tab-content" id="configTabsContent">
        
        {{-- ============================================ --}}
        {{-- PANEL DE EMPRESA (SOLO ADMIN) --}}
        {{-- ============================================ --}}
        @if(auth()->user()->isAdmin())
        <div class="tab-pane fade show active" id="panel-empresa" role="tabpanel">
            <form action="{{ route('configuracion.empresa.update') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <div class="card form-card p-4">
                    <div class="row g-4">
                        
                        <!-- Columna de Logo -->
                        <div class="col-12 col-md-3 text-center border-end border-sm-0 pb-3 pb-md-0">
                            <div class="section-title text-start mb-3">
                                <i class="bi bi-image me-2"></i>Logotipo
                            </div>
                            <div class="mb-3 d-flex justify-content-center align-items-center bg-body-tertiary rounded-4 mx-auto" style="width: 160px; height: 160px; overflow: hidden; border: 2px dashed var(--bs-border-color);">
                                @if(\App\Helpers\ContentHelper::getCompanyData('empresa_logo'))
                                    <img id="logo-preview" src="{{ asset('storage/' . \App\Helpers\ContentHelper::getCompanyData('empresa_logo')) }}" class="img-fluid h-100 w-100 object-fit-cover" alt="Logo corporativo">
                                @else
                                    <img id="logo-preview" src="" class="img-fluid h-100 w-100 object-fit-cover d-none" alt="Vista previa del logo">
                                    <i id="logo-placeholder" class="bi bi-building text-secondary opacity-25" style="font-size: 4rem;"></i>
                                @endif
                            </div>
                            <input type="file" name="empresa_logo" id="empresa_logo" class="form-control form-control-sm bg-body text-body" accept="image/*">
                            <small class="text-secondary d-block mt-2" style="font-size: 11px;">Formatos: PNG, JPG (Max 2MB)</small>
                        </div>

                        <!-- Columna de Datos -->
                        <div class="col-12 col-md-9">
                            
                            <!-- SECCIÓN: IDENTIDAD -->
                            <div class="section-title">
                                <i class="bi bi-card-heading me-2"></i>Identidad Corporativa
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold text-body">Nombre de la Empresa o Razón Social <span class="text-danger">*</span></label>
                                    <input type="text" name="empresa_nombre" class="form-control form-control-sm bg-body text-body" placeholder="Ej. Corporativo Viramontes S.A." value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_nombre') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold text-body">Dueño / Representante Legal</label>
                                    <input type="text" name="empresa_dueno" class="form-control form-control-sm bg-body text-body" placeholder="Ej. Juan Pérez" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_dueno') }}">
                                </div>
                            </div>

                            <!-- SECCIÓN: FISCAL Y CONTACTO -->
                            <div class="section-title">
                                <i class="bi bi-file-earmark-person me-2"></i>Datos Fiscales y Contacto
                            </div>
                            <div class="row g-3 mb-4">
                                <!-- Validación Visual: RFC -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-body">RFC / Identificación Fiscal</label>
                                    <input type="text" name="empresa_rfc" class="form-control form-control-sm bg-body text-body text-uppercase validar-rfc @error('empresa_rfc') is-invalid @enderror" placeholder="12 o 13 caracteres" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_rfc') }}" maxlength="13">
                                    <div class="invalid-feedback">Formato de RFC inválido (Ej: ABC680524P36).</div>
                                </div>

                                <!-- Validación Visual: Teléfono -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-body">Teléfono Corporativo</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="empresa_telefono" class="form-control bg-body text-body border-start-0 validar-telefono @error('empresa_telefono') is-invalid @enderror" placeholder="10 dígitos" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_telefono') }}" maxlength="10">
                                        <div class="invalid-feedback">Debe contener exactamente 10 dígitos.</div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-12">
                                    <label class="form-label small fw-semibold text-body">Dirección Fiscal / Matriz</label>
                                    <input type="text" name="empresa_direccion" class="form-control form-control-sm bg-body text-body" placeholder="Calle, Número, Colonia, C.P., Ciudad" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_direccion') }}">
                                </div>
                            </div>

                            <!-- Botón Guardar -->
                            <div class="d-flex justify-content-end border-top pt-3 mt-2">
                                <button type="submit" class="btn btn-success btn-sm px-4 fw-bold rounded-3 shadow-sm">
                                    <i class="bi bi-check-lg me-1"></i> Guardar Configuración
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- PANEL DE SUCURSALES --}}
        {{-- ============================================ --}}
        <div class="tab-pane fade {{ (auth()->user()->isGerente() && !auth()->user()->isAdmin()) ? 'show active' : '' }}" id="panel-sucursales" role="tabpanel">
            <div class="card form-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="section-title border-0 mb-0"><i class="bi bi-shop me-2"></i>Unidades de Negocio</h5>
                    @if(auth()->user()->isAdmin())
                        <button class="btn btn-primary btn-sm shadow-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearSucursal">
                            <i class="bi bi-plus-lg me-1"></i> Nueva Sucursal
                        </button>
                    @endif
                </div>

                <div class="row">
                    @forelse($sucursales as $suc)
                        <div class="col-12 col-lg-6">
                            <div class="branch-card">
                                <div class="d-flex align-items-center gap-3 w-100 text-truncate">
                                    <div class="media-frame flex-shrink-0">
                                        @if($suc->logo)
                                            <img src="{{ asset('storage/' . $suc->logo) }}" alt="Logo">
                                        @else
                                            <i class="bi bi-geo-alt-fill text-secondary opacity-50 fs-4"></i>
                                        @endif
                                    </div>
                                    <div class="text-truncate">
                                        <h6 class="fw-bold mb-1 text-body text-truncate">{{ $suc->nombre }}</h6>
                                        <p class="text-secondary mb-0 d-flex flex-wrap gap-3" style="font-size: 11px;">
                                            <span><i class="bi bi-pin-map text-primary me-1"></i>{{ Str::limit($suc->direccion, 30) }}</span>
                                            @if($suc->telefono)
                                                <span><i class="bi bi-telephone text-success me-1"></i>{{ $suc->telefono }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 ms-3 flex-shrink-0">
                                    <span class="badge rounded-pill {{ $suc->activa ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}" style="font-size: 11px;">
                                        {{ $suc->activa ? 'Activa' : 'Inactiva' }}
                                    </span>
                                    <button class="btn btn-outline-secondary btn-sm border rounded-3 p-1 px-2" data-bs-toggle="modal" data-bs-target="#modalEditarSucursal{{ $suc->id }}" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL EDITAR SUCURSAL --}}
                        <div class="modal fade" id="modalEditarSucursal{{ $suc->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header bg-body-tertiary border-bottom py-3">
                                        <h6 class="modal-title fw-bold"><i class="bi bi-building-gear text-primary me-2"></i>Actualizar Sucursal</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('configuracion.sucursal.update', $suc->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                        @csrf @method('PUT')
                                        <div class="modal-body p-4 bg-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-body">Nombre de la Sucursal <span class="text-danger">*</span></label>
                                                <input type="text" name="nombre" class="form-control form-control-sm" value="{{ $suc->nombre }}" required>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-body">RFC de Facturación</label>
                                                    <input type="text" name="rfc" class="form-control form-control-sm validar-rfc text-uppercase" value="{{ $suc->rfc }}" maxlength="13">
                                                    <div class="invalid-feedback">Formato de RFC inválido.</div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-body">Teléfono Atención</label>
                                                    <input type="text" name="telefono" class="form-control form-control-sm validar-telefono" value="{{ $suc->telefono }}" maxlength="10">
                                                    <div class="invalid-feedback">10 dígitos requeridos.</div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-body">Dirección Completa <span class="text-danger">*</span></label>
                                                <input type="text" name="direccion" class="form-control form-control-sm" value="{{ $suc->direccion }}" required>
                                            </div>
                                            <div class="row g-2 align-items-end">
                                                <div class="{{ auth()->user()->isAdmin() ? 'col-8' : 'col-12' }}">
                                                    <label class="form-label fw-semibold small text-muted">Cambiar Logo</label>
                                                    <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                                                </div>
                                                @if(auth()->user()->isAdmin())
                                                    <div class="col-4">
                                                        <label class="form-label fw-semibold small text-muted">Estado</label>
                                                        <select name="activa" class="form-select form-select-sm">
                                                            <option value="1" {{ $suc->activa ? 'selected' : '' }}>Operativa</option>
                                                            <option value="0" {{ !$suc->activa ? 'selected' : '' }}>Suspendida</option>
                                                        </select>
                                                    </div>
                                                @else
                                                    <input type="hidden" name="activa" value="{{ $suc->activa ? 1 : 0 }}">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer py-2 bg-body-tertiary border-top">
                                            <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold rounded-3 px-4">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 border border-dashed rounded-4 bg-body-tertiary">
                            <i class="bi bi-shop fs-1 text-secondary mb-3 d-block opacity-50"></i>
                            <h6 class="fw-bold text-dark">No hay sucursales registradas</h6>
                            <p class="text-muted small mx-auto mb-0" style="max-width: 360px;">Registra tu primera unidad de negocio para poder enlazar inventarios y operadores.</p>
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
            <div class="card config-card shadow-sm p-3 p-md-4 mb-4">
                
                <!-- Header de la sección de Usuarios -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div class="section-title border-bottom-0 mb-0 pb-0">
                        <i class="bi bi-people me-2"></i>Gestión de Personal
                    </div>
                    <button class="btn btn-primary btn-sm rounded-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                        <i class="bi bi-person-plus-fill me-1"></i> Registrar Empleado
                    </button>
                </div>

                <!-- Tabla Estilo "Clientes" -->
                <div class="card border shadow-sm rounded-3 overflow-hidden" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
                            <thead class="bg-body-tertiary text-body-secondary border-bottom">
                                <tr>
                                    <th class="ps-3 py-2.5">Nombre</th>
                                    <th class="py-2.5">Email Corporativo</th>
                                    <th class="py-2.5">Sucursal Asignada</th>
                                    <th class="py-2.5">Rol Sistema</th>
                                    <th class="py-2.5">Estado</th>
                                    <th class="text-center pe-3 py-2.5" style="width: 140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuarios as $user)
                                <tr class="border-bottom {{ $user->status != 'activo' ? 'bg-body-tertiary bg-opacity-50' : '' }}">
                                    
                                    <!-- Nombre y Avatar -->
                                    <td class="ps-3 py-2.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-icon shadow-sm border" style="width: 36px; height: 36px; border-radius: 10px; font-size: 14px;">
                                                @if($user->foto)
                                                    <img src="{{ asset('storage/' . $user->foto) }}" class="w-100 h-100 object-fit-cover" style="border-radius: 10px;" alt="Avatar">
                                                @else
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                @endif
                                            </div>
                                            <div>
                                                <strong class="text-body d-block text-truncate" style="max-width: 150px;">{{ $user->name }}</strong>
                                                <small class="text-body-secondary" style="font-size: 10px;">ID: #{{ $user->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Email -->
                                    <td class="py-2.5 text-secondary small text-truncate" style="max-width: 160px;">
                                        <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                    </td>
                                    
                                    <!-- Sucursal -->
                                    <td class="py-2.5">
                                        <span class="fw-semibold text-body small">
                                            <i class="bi bi-geo-alt text-secondary me-1"></i>{{ $user->sucursal->nombre ?? 'Sin asignar' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Rol -->
                                    <td class="py-2.5">
                                        @if($user->role == 'admin')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Admin</span>
                                        @elseif($user->role == 'gerente')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Gerente</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Cajero</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Estado -->
                                    <td class="py-2.5">
                                        <span class="badge {{ $user->status == 'activo' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} rounded-pill px-2.5 py-1" style="font-size: 10.5px;">
                                            @if($user->status == 'activo')
                                                <i class="bi bi-check-circle-fill me-1"></i> Activo
                                            @else
                                                <i class="bi bi-slash-circle-fill me-1"></i> Inhabilitado
                                            @endif
                                        </span>
                                    </td>
                                    
                                    <!-- Botones de Acción (ESTILO CLIENTES) -->
                                    <td class="text-center pe-3 py-2.5">
                                        <div class="d-flex justify-content-center align-items-center gap-1">
                                            
                                            <button class="btn btn-sm btn-outline-secondary rounded-3 px-2" title="Cambiar Contraseña" data-bs-toggle="modal" data-bs-target="#modalPassword{{ $user->id }}">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            
                                            <button class="btn btn-sm btn-outline-primary rounded-3 px-2" title="Editar Operador" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario{{ $user->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            
                                            @if($user->status == 'activo')
                                                <form action="{{ route('configuracion.usuarios.baja', $user->id) }}" method="POST" class="d-inline m-0">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2" title="Dar de Baja" onclick="return confirm('¿Suspender accesos al sistema para este operador?')">
                                                        <i class="bi bi-person-x"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('configuracion.usuarios.alta', $user->id) }}" method="POST" class="d-inline m-0">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2" title="Reactivar Operador">
                                                        <i class="bi bi-person-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                        </div>
                                    </td>
                                </tr>
                                @include('configuracion.partials.modales_usuario', ['user' => $user])
                                
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-body-secondary py-5">
                                        <i class="bi bi-people fs-1 d-block mb-2 text-body-tertiary"></i>
                                        No hay operadores registrados.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- PANEL DE PLANTILLAS --}}
        {{-- ============================================ --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isGerente())
        <div class="tab-pane fade" id="panel-plantillas" role="tabpanel">
            <div class="card form-card p-4">
                <div class="section-title mb-4">
                    <i class="bi bi-file-earmark-text me-2"></i>Estructura Legal de Documentos
                </div>
                
                <div class="row g-4">
                    @forelse($plantillas as $p)
                    <div class="col-12 col-xl-6">
                        <div class="card border p-3 p-md-4 rounded-4 bg-body-tertiary h-100 shadow-sm">
                            <form action="{{ route('configuracion.plantilla.update', $p->id) }}" method="POST" class="d-flex flex-column h-100 m-0">
                                @csrf @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-primary text-uppercase" style="font-size: 11px;">
                                        <i class="bi bi-card-text me-1"></i> Tipo: {{ str_replace('_', ' ', $p->tipo) }}
                                    </label>
                                    <input type="text" name="titulo" class="form-control bg-body fw-semibold" value="{{ $p->titulo }}" placeholder="Título oficial del documento">
                                </div>
                                <div class="mb-3 flex-grow-1">
                                    <label class="form-label text-secondary small fw-semibold mb-1">Cláusulas Editables</label>
                                    <textarea name="contenido" class="form-control font-monospace small bg-body" rows="12" style="font-size: 13px; line-height: 1.5; resize: vertical;" required>{{ $p->contenido }}</textarea>
                                </div>
                                
                                <div class="p-3 bg-body border rounded-3 mb-3">
                                    <span class="d-block fw-bold text-secondary mb-2" style="font-size: 11px; text-transform: uppercase;">
                                        <i class="bi bi-braces text-primary me-1"></i> Variables Dinámicas:
                                    </span>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="badge btn btn-outline-secondary font-monospace" onclick="insertVariable(this, '{cliente}')">{cliente}</button>
                                        <button type="button" class="badge btn btn-outline-secondary font-monospace" onclick="insertVariable(this, '{folio}')">{folio}</button>
                                        <button type="button" class="badge btn btn-outline-secondary font-monospace" onclick="insertVariable(this, '{deposito}')">{deposito}</button>
                                        <button type="button" class="badge btn btn-outline-secondary font-monospace" onclick="insertVariable(this, '{monto_total}')">{monto_total}</button>
                                        <button type="button" class="badge btn btn-outline-secondary font-monospace" onclick="insertVariable(this, '{fecha_inicio}')">{fecha_inicio}</button>
                                        <button type="button" class="badge btn btn-outline-secondary font-monospace" onclick="insertVariable(this, '{fecha_fin}')">{fecha_fin}</button>
                                        <button type="button" class="badge btn btn-outline-secondary font-monospace" onclick="insertVariable(this, '{empresa}')">{empresa}</button>
                                        <!-- NUEVA VARIABLE DUEÑO -->
                                        <button type="button" class="badge btn btn-outline-primary font-monospace" onclick="insertVariable(this, '{dueno_empresa}')">{dueno_empresa}</button>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-dark btn-sm w-100 py-2 fw-bold mt-auto rounded-3">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Actualizar Plantilla
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5 border border-dashed rounded-4 bg-body-tertiary">
                        <i class="bi bi-file-earmark-x text-secondary fs-1 mb-2"></i>
                        <h6 class="fw-bold text-body">No hay plantillas registradas</h6>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ============================================ --}}
{{-- MODAL CREAR SUCURSAL (SOLO ADMIN) --}}
{{-- ============================================ --}}
@if(auth()->user()->isAdmin())
<div class="modal fade" id="modalCrearSucursal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3 rounded-top-4">
                <h6 class="modal-title fw-bold"><i class="bi bi-building-add me-2"></i>Registrar Unidad de Negocio</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('configuracion.sucursal.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4 bg-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Nombre Comercial <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control form-control-sm" required placeholder="Ej: Sucursal Centro">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body">RFC Sucursal</label>
                            <input type="text" name="rfc" class="form-control form-control-sm validar-rfc text-uppercase" placeholder="Opcional" maxlength="13">
                            <div class="invalid-feedback">Formato inválido.</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body">Teléfono</label>
                            <input type="text" name="telefono" class="form-control form-control-sm validar-telefono" placeholder="10 dígitos" maxlength="10">
                            <div class="invalid-feedback">10 dígitos requeridos.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Dirección Geográfica <span class="text-danger">*</span></label>
                        <input type="text" name="direccion" class="form-control form-control-sm" required placeholder="Calle, Número, Colonia, C.P.">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-body">Logotipo Especifico (Opcional)</label>
                        <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-secondary px-3 rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-4 rounded-3">Registrar Sucursal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL CREAR USUARIO --}}
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3 rounded-top-4">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Alta de Nuevo Empleado</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('configuracion.usuarios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 bg-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Contraseña Temporal <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control form-control-sm" required minlength="6">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body">Rol <span class="text-danger">*</span></label>
                            <select name="role" class="form-select form-select-sm" required>
                                <option value="cajero" selected>Cajero</option>
                                <option value="gerente">Gerente de Sucursal</option>
                                <option value="admin">Administrador Global</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body">Sucursal <span class="text-danger">*</span></label>
                            <select name="sucursal_id" class="form-select form-select-sm" required>
                                <option value="" disabled selected>Seleccione...</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small text-muted">Fotografía (Opcional)</label>
                        <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-secondary px-3 rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold px-4 rounded-3">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
    // Inserción de variables en Textarea (Plantillas)
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
        // Gestión de Pestañas Activas (LocalStorage)
        let activeTab = "{{ session('tab') }}" || localStorage.getItem('activeConfigTab');
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

        // Previsualización de Logo de Empresa
        const inputLogo = document.getElementById('empresa_logo');
        if(inputLogo) {
            inputLogo.addEventListener('change', function() {
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
        }

        // =========================================================
        // SCRIPT GENÉRICO DE VALIDACIÓN VISUAL (Múltiples Formularios)
        // =========================================================
        const regexTel = /^\d{10}$/;
        const regexRFC = /^([A-Z&Ñ]{3,4})\d{6}([A-Z0-9]{3})$/i;

        // Aplica o quita las clases is-valid / is-invalid
        function validarCampoVisual(input, regex, esOpcional = false) {
            if (!input) return true;
            const value = input.value.trim();
            
            if (esOpcional && value === '') {
                input.classList.remove('is-invalid', 'is-valid');
                return true;
            }

            const isValid = regex.test(value);
            if (isValid) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
            return isValid;
        }

        // Inicializador de eventos para inputs específicos
        function setupValidation(selector, regex, formatFn) {
            document.querySelectorAll(selector).forEach(input => {
                input.addEventListener('input', function() {
                    if (formatFn) this.value = formatFn(this.value);
                    // Todos estos campos los tomamos como opcionales visualmente para no obligar si el sistema lo permite
                    validarCampoVisual(this, regex, true); 
                });
            });
        }

        // 1. Aplicar a todos los inputs con clase .validar-telefono
        setupValidation('.validar-telefono', regexTel, val => val.replace(/\D/g, ''));
        
        // 2. Aplicar a todos los inputs con clase .validar-rfc
        setupValidation('.validar-rfc', regexRFC, val => val.toUpperCase());

        // Bloquear envíos de formulario si hay campos inválidos
        document.querySelectorAll('form.needs-validation').forEach(form => {
            form.addEventListener('submit', function (event) {
                let formValido = true;
                
                // Verificar campos teléfono dentro de este form
                form.querySelectorAll('.validar-telefono').forEach(input => {
                    if (!validarCampoVisual(input, regexTel, true)) formValido = false;
                });

                // Verificar campos RFC dentro de este form
                form.querySelectorAll('.validar-rfc').forEach(input => {
                    if (!validarCampoVisual(input, regexRFC, true)) formValido = false;
                });

                if (!formValido) {
                    event.preventDefault();
                    event.stopPropagation();
                    const primerError = form.querySelector('.is-invalid');
                    if (primerError) primerError.focus();
                }
            });
        });
    });
</script>
@endsection