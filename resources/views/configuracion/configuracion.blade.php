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
    .btn-variable {
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
        border: 1px solid var(--bs-border-color);
        transition: all 0.2s ease;
    }
    .btn-variable:hover {
        background-color: var(--bs-primary);
        color: #ffffff !important;
        border-color: var(--bs-primary);
        transform: translateY(-2px); /* Pequeño salto al pasar el mouse */
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
    }

    /* =========================================================
    EDITOR DEL PAGARÉ
    ========================================================= */

    .pagare-editor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }

    .pagare-editor-tabs {
        display: flex;
        gap: 8px;
        padding: 8px;
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        background: var(--bs-tertiary-bg);
    }

    .pagare-editor-tabs .nav-item {
        flex: 1;
    }

    .pagare-editor-tabs .nav-link {
        width: 100%;
        border-radius: 8px;
        color: var(--bs-body-color);
        font-size: 13px;
        font-weight: 600;
        padding: 10px;
    }

    .pagare-editor-tabs .nav-link.active {
        background: var(--bs-primary);
        color: #fff;
    }


    .editor-section {
        border: 1px solid var(--bs-border-color);
        border-radius: 14px;
        padding: 22px;
        background: var(--bs-body-bg);
    }

    .editor-section-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--bs-primary);
    }

    .editor-section-title span {
        display: inline-flex;
        justify-content: center;
        align-items: center;

        width: 25px;
        height: 25px;

        margin-right: 6px;

        border-radius: 50%;

        background: var(--bs-primary);
        color: white;

        font-size: 12px;
    }


    .pagare-preview-card,
    .pagare-variable-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 14px;
        padding: 18px;
        background: var(--bs-tertiary-bg);
    }


    .mini-pagare {
        background: #e8f5e9;
        border: 3px solid #2e7d32;
        border-radius: 9px;
        padding: 14px;
        color: #1b5e20;
        font-size: 10px;
    }

    .mini-title {
        background: white;
        border: 2px solid #2e7d32;
        border-radius: 6px;
        padding: 4px 9px;

        font-size: 15px;
        font-weight: bold;
        font-style: italic;
    }

    .mini-amount {
        background: white;
        border: 2px solid #2e7d32;
        border-radius: 5px;

        margin-top: 3px;
        padding: 3px 12px;

        font-weight: bold;
    }

    .mini-line {
        text-align: center;
        border-bottom: 1px solid rgba(46, 125, 50, .35);
        padding-bottom: 5px;
    }

    .mini-body {
        line-height: 1.5;
    }


    .pagare-variable {
        border-radius: 20px;
        font-family: monospace;
        font-size: 11px;
    }


    @media (max-width: 991px) {

        .pagare-editor-tabs {
            flex-direction: column;
        }

        .pagare-editor-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    .campo-variable-activo {
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .20) !important;
    }
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
                                <i class="bi bi-card-heading me-2"></i>Identidad Corporativa y Sistema
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-body">Nombre de la Empresa <span class="text-danger">*</span></label>
                                    <input type="text" name="empresa_nombre" class="form-control form-control-sm bg-body text-body" placeholder="Ej. Corporativo Viramontes S.A." value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_nombre') }}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-body">Dueño / Representante Legal</label>
                                    <input type="text" name="empresa_dueno" class="form-control form-control-sm bg-body text-body" placeholder="Ej. Juan Pérez" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_dueno') }}">
                                </div>
                                <!-- NUEVO CAMPO: FOLIO GLOBAL -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-body">Folio Inicial Global (Rentas)</label>
                                    <input type="number" name="folio_global_rentas" class="form-control form-control-sm bg-body text-body" placeholder="Mínimo 4000" min="4000" value="{{ \App\Helpers\ContentHelper::getCompanyData('folio_global_rentas', '4000') }}">
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
                                                    <label class="form-label fw-semibold small text-muted">Logotipo actual / Cambiar</label>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="border rounded-2 d-flex align-items-center justify-content-center overflow-hidden bg-body-tertiary flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                                                            @if($suc->logo)
                                                                <img id="preview-sucursal-{{ $suc->id }}" src="{{ asset('storage/' . $suc->logo) }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Logo">
                                                            @else
                                                                <img id="preview-sucursal-{{ $suc->id }}" src="" class="img-fluid w-100 h-100 object-fit-cover d-none" alt="Preview">
                                                                <i id="icon-sucursal-{{ $suc->id }}" class="bi bi-image text-secondary opacity-50"></i>
                                                            @endif
                                                        </div>
                                                        <input type="file" name="logo" class="form-control form-control-sm w-100" accept="image/*" onchange="previewImageGlobal(this, 'preview-sucursal-{{ $suc->id }}', 'icon-sucursal-{{ $suc->id }}')">
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label fw-semibold small text-muted">Folio Inicial Rentas</label>
                                                    <input type="number" name="siguiente_folio_rentas" class="form-control form-control-sm" value="{{ $suc->siguiente_folio_rentas }}" placeholder="Dejar en blanco para autogenerar">
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

        {{-- =========================================================
            PANEL DE PLANTILLAS DE DOCUMENTOS
        ========================================================= --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isGerente())

        <div class="tab-pane fade" id="panel-plantillas" role="tabpanel">

            <div class="card form-card p-4">

                <div class="section-title mb-4">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Plantillas de Documentos
                </div>

                @foreach($plantillas as $p)

                    {{-- =====================================================
                        PAGARÉ - EDITOR AVANZADO
                    ====================================================== --}}
                    @if($p->tipo === 'pagare')

                        @php
                            $pc = $pagareConfig ?? [];

                            $valor = function($key, $default = '') use ($pc) {
                                return old($key, $pc[$key] ?? $default);
                            };
                        @endphp


                        <form
                            action="{{ route('configuracion.plantilla.update', $p->id) }}"
                            method="POST"
                            id="formPagare"
                            class="mb-4"
                        >

                            @csrf
                            @method('PUT')

                            <input
                                type="hidden"
                                name="titulo"
                                id="plantillaTituloPagare"
                                value="{{ $p->titulo ?? 'PAGARÉ' }}"
                            >


                            {{-- CABECERA DEL EDITOR --}}
                            <div class="pagare-editor-header mb-4">

                                <div>
                                    <h5 class="mb-1 fw-bold">
                                        <i class="bi bi-file-earmark-text me-2"></i>
                                        Configuración del Pagaré
                                    </h5>

                                    <small class="text-secondary">
                                        Personaliza cada elemento del documento
                                    </small>
                                </div> 

                            </div>


                            {{-- =================================================
                                NAVEGACIÓN INTERNA
                            ================================================== --}}
                            <ul
                                class="nav nav-pills pagare-editor-tabs mb-4"
                                id="pagareEditorTabs"
                                role="tablist"
                            >

                                <li class="nav-item">
                                    <button
                                        class="nav-link active"
                                        id="pagare-encabezado-tab"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pagare-encabezado"
                                        type="button"
                                        role="tab"
                                        aria-controls="pagare-encabezado"
                                        aria-selected="true"
                                    >
                                        <i class="bi bi-card-heading me-1"></i>
                                        Encabezado
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <button
                                        class="nav-link"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pagare-cuerpo"
                                        type="button"
                                    >
                                        <i class="bi bi-text-paragraph me-1"></i>
                                        Cuerpo de Texto
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <button
                                        class="nav-link"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pagare-deudor"
                                        type="button"
                                    >
                                        <i class="bi bi-person-vcard me-1"></i>
                                        Datos del Deudor
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <button
                                        class="nav-link"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pagare-firma"
                                        type="button"
                                    >
                                        <i class="bi bi-pen me-1"></i>
                                        Pie y Firmas
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <button
                                        class="nav-link"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pagare-apariencia"
                                        type="button"
                                    >
                                        <i class="bi bi-palette me-1"></i>
                                        Apariencia
                                    </button>
                                </li>

                            </ul>


                            <div class="row g-4">

                                {{-- ==============================================
                                    EDITOR IZQUIERDO
                                =============================================== --}}
                                <div class="col-12 col-xl-8">

                                    <div class="tab-content">


                                        {{-- =========================================
                                            1. ENCABEZADO
                                        ========================================== --}}
                                        <div
                                        class="tab-pane fade show active"
                                        id="pagare-encabezado"
                                        role="tabpanel"
                                        aria-labelledby="pagare-encabezado-tab"
                                    >

                                        <div class="editor-section">

                                            <div class="editor-section-title">
                                                <span>1</span>
                                                Encabezado
                                            </div>

                                            <p class="text-secondary small">
                                                Personaliza textos y valores dinámicos del encabezado.
                                            </p>


                                            <div class="row g-3">

                                                {{-- TÍTULO --}}
                                                <div class="col-md-6">

                                                    <label class="form-label">
                                                        Título del documento
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_titulo"
                                                        id="pagare_titulo"
                                                        class="form-control pagare-live"
                                                        value="{{ $valor(
                                                            'pagare_titulo',
                                                            'PAGARÉ'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- BUENO POR --}}
                                                <div class="col-md-6">

                                                    <label class="form-label">
                                                        Texto del importe
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_texto_bueno_por"
                                                        id="pagare_texto_bueno_por"
                                                        class="form-control pagare-live"
                                                        value="{{ $valor(
                                                            'pagare_texto_bueno_por',
                                                            'BUENO POR $'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- ETIQUETA NÚMERO --}}
                                                <div class="col-md-4">

                                                    <label class="form-label">
                                                        Etiqueta número
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_etiqueta_numero"
                                                        id="pagare_etiqueta_numero"
                                                        class="form-control pagare-live"
                                                        value="{{ $valor(
                                                            'pagare_etiqueta_numero',
                                                            'No.'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- VALOR NÚMERO --}}
                                                <div class="col-md-4">

                                                    <label class="form-label fw-bold">
                                                        Valor del número
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_numero"
                                                        id="pagare_valor_numero"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_valor_numero',
                                                            '1/1'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- VALOR DEL MONTO --}}
                                                <div class="col-md-4">

                                                    <label class="form-label fw-bold">
                                                        Valor del importe
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_importe"
                                                        id="pagare_valor_importe"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_importe',
                                                            '{monto_total}'
                                                        ) }}"
                                                    >

                                                    <small class="text-secondary">
                                                        Ejemplo: {monto_total}
                                                    </small>

                                                </div>

                                            </div>


                                            <hr class="my-4">


                                            <h6 class="fw-bold mb-3">
                                                Lugar y fecha de expedición
                                            </h6>


                                            <div class="row g-3">

                                                {{-- TEXTO EN --}}
                                                <div class="col-md-3">

                                                    <label class="form-label">
                                                        Texto inicial
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_texto_en"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_texto_en',
                                                            'En'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- VALOR CIUDAD --}}
                                                <div class="col-md-9">

                                                    <label class="form-label fw-bold">
                                                        Valor del lugar de expedición
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_lugar_expedicion"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_lugar_expedicion',
                                                            '{ciudad_cliente}'
                                                        ) }}"
                                                    >

                                                    <small class="text-secondary">
                                                        Puedes usar {ciudad_cliente}, {lugar_expedicion} o texto fijo.
                                                    </small>

                                                </div>


                                                {{-- TEXTO A --}}
                                                <div class="col-md-3">

                                                    <label class="form-label">
                                                        Texto antes del día
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_texto_a"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_texto_a',
                                                            'a'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- VALOR DÍA --}}
                                                <div class="col-md-3">

                                                    <label class="form-label fw-bold">
                                                        Valor del día
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_dia_expedicion"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_dia_expedicion',
                                                            '{dia_expedicion}'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- TEXTO DE --}}
                                                <div class="col-md-2">

                                                    <label class="form-label">
                                                        Texto
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_texto_de_mes"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_texto_de_mes',
                                                            'de'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- VALOR MES --}}
                                                <div class="col-md-4">

                                                    <label class="form-label fw-bold">
                                                        Valor del mes
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_mes_expedicion"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_mes_expedicion',
                                                            '{mes_expedicion}'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- TEXTO DE AÑO --}}
                                                <div class="col-md-3">

                                                    <label class="form-label">
                                                        Texto antes del año
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_texto_de_anio"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_texto_de_anio',
                                                            'de'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- VALOR AÑO --}}
                                                <div class="col-md-4">

                                                    <label class="form-label fw-bold">
                                                        Valor del año
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_anio_expedicion"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_anio_expedicion',
                                                            '{anio_expedicion}'
                                                        ) }}"
                                                    >

                                                </div>


                                                {{-- ETIQUETA EXPEDICIÓN --}}
                                                <div class="col-md-5">

                                                    <label class="form-label">
                                                        Leyenda inferior
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_etiqueta_expedicion"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_etiqueta_expedicion',
                                                            'Lugar y fecha de expedición'
                                                        ) }}"
                                                    >

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                        {{-- =========================================
                                            2. CUERPO
                                        ========================================== --}}
                                        <div
                                            class="tab-pane fade"
                                            id="pagare-cuerpo"
                                        >

                                            <div class="editor-section">

                                                <div class="editor-section-title">
                                                    <span>2</span>
                                                    Cuerpo del pagaré
                                                </div>


                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        Texto principal
                                                    </label>

                                                    <textarea
                                                        name="pagare_texto_promesa"
                                                        class="form-control"
                                                        rows="4"
                                                    >{{ $valor(
                                                        'pagare_texto_promesa',
                                                        'Debo(mos) y pagaré(mos) incondicionalmente por este Pagaré a la orden de'
                                                    ) }}</textarea>

                                                    <small class="text-secondary">
                                                        Este texto aparece antes del nombre de la empresa.
                                                    </small>

                                                </div>


                                                <div class="row g-3">
                                                {{-- =====================================================
                                                    BENEFICIARIO
                                                ====================================================== --}}
                                                <div class="col-md-6">

                                                    <label class="form-label">
                                                        Etiqueta beneficiario
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_etiqueta_beneficiario"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_etiqueta_beneficiario',
                                                            'Nombre de la persona a quien ha de pagarse'
                                                        ) }}"
                                                    >

                                                </div>


                                                <div class="col-md-6">

                                                    <label class="form-label fw-bold">
                                                        Valor del beneficiario
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_beneficiario"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_beneficiario',
                                                            '{empresa}'
                                                        ) }}"
                                                        placeholder="{empresa}"
                                                    >

                                                    <small class="text-secondary">
                                                        Puedes usar por ejemplo {empresa}, {dueno_empresa} o escribir texto fijo.
                                                    </small>

                                                </div>


                                                {{-- =====================================================
                                                    LUGAR DE PAGO
                                                ====================================================== --}}
                                                <div class="col-md-6">

                                                    <label class="form-label">
                                                        Etiqueta lugar de pago
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_etiqueta_lugar_pago"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_etiqueta_lugar_pago',
                                                            'Lugar de pago'
                                                        ) }}"
                                                    >

                                                </div>


                                                <div class="col-md-6">

                                                    <label class="form-label fw-bold">
                                                        Valor del lugar de pago
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_lugar_pago"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_lugar_pago',
                                                            '{ciudad_cliente}'
                                                        ) }}"
                                                        placeholder="{ciudad_cliente}"
                                                    >

                                                    <small class="text-secondary">
                                                        Ejemplo: {ciudad_cliente}, {lugar_expedicion} o texto fijo.
                                                    </small>

                                                </div>


                                                {{-- =====================================================
                                                    FECHA DE PAGO
                                                ====================================================== --}}
                                                <div class="col-md-6">

                                                    <label class="form-label">
                                                        Etiqueta fecha de pago
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_etiqueta_fecha_pago"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_etiqueta_fecha_pago',
                                                            'Fecha de pago'
                                                        ) }}"
                                                    >

                                                </div>


                                                <div class="col-md-6">

                                                    <label class="form-label fw-bold">
                                                        Valor de la fecha de pago
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_fecha_pago"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_fecha_pago',
                                                            '{fecha_fin}'
                                                        ) }}"
                                                        placeholder="{fecha_fin}"
                                                    >

                                                    <small class="text-secondary">
                                                        Puedes usar {fecha_fin}, {fecha_inicio} o {fecha_pago}.
                                                    </small>

                                                </div>


                                                {{-- =====================================================
                                                    CANTIDAD
                                                ====================================================== --}}
                                                <div class="col-md-6">

                                                    <label class="form-label">
                                                        Texto antes de cantidad
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_texto_cantidad"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_texto_cantidad',
                                                            'La cantidad de:'
                                                        ) }}"
                                                    >

                                                </div>


                                                <div class="col-md-6">

                                                    <label class="form-label fw-bold">
                                                        Valor del pagaré
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_monto"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_monto',
                                                            '{monto_total}'
                                                        ) }}"
                                                        placeholder="{monto_total}"
                                                    >

                                                    <small class="text-success">
                                                        Para usar el total completo de la renta deja {monto_total}.
                                                    </small>

                                                </div>


                                                {{-- =====================================================
                                                    CANTIDAD EN LETRAS
                                                ====================================================== --}}
                                                <div class="col-md-6">

                                                    <label class="form-label fw-bold">
                                                        Cantidad en letras
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_valor_monto_letras"
                                                        class="form-control font-monospace"
                                                        value="{{ $valor(
                                                            'pagare_valor_monto_letras',
                                                            '{monto_total_letras}'
                                                        ) }}"
                                                        placeholder="{monto_total_letras}"
                                                    >

                                                </div>


                                                <div class="col-md-6">

                                                    <label class="form-label">
                                                        Leyenda debajo del importe
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="pagare_texto_importe"
                                                        class="form-control"
                                                        value="{{ $valor(
                                                            'pagare_texto_importe',
                                                            'Importe correspondiente al total de la renta.'
                                                        ) }}"
                                                    >

                                                </div>

                                            </div>


                                                <hr class="my-4">


                                                <div class="mb-3">

                                                    <label class="form-label fw-bold">
                                                        Cláusula legal
                                                    </label>

                                                    <textarea
                                                        name="pagare_clausula_legal"
                                                        id="pagare_clausula_legal"
                                                        class="form-control font-monospace"
                                                        rows="9"
                                                    >{{ $valor(
                                                        'pagare_clausula_legal',
                                                        $p->contenido
                                                    ) }}</textarea>

                                                </div>

                                            </div>

                                        </div>



                                        {{-- =========================================
                                            3. DEUDOR
                                        ========================================== --}}
                                        <div
                                            class="tab-pane fade"
                                            id="pagare-deudor"
                                        >

                                            <div class="editor-section">

                                                <div class="editor-section-title">
                                                    <span>3</span>
                                                    Datos del deudor
                                                </div>


                                                <div class="row g-3">

                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            Título
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="pagare_titulo_deudor"
                                                            class="form-control"
                                                            value="{{ $valor('pagare_titulo_deudor', 'Datos del deudor') }}"
                                                        >
                                                    </div>


                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            Etiqueta nombre
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="pagare_etiqueta_nombre"
                                                            class="form-control"
                                                            value="{{ $valor('pagare_etiqueta_nombre', 'Nombre:') }}"
                                                        >
                                                    </div>


                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            Etiqueta dirección
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="pagare_etiqueta_direccion"
                                                            class="form-control"
                                                            value="{{ $valor('pagare_etiqueta_direccion', 'Dirección:') }}"
                                                        >
                                                    </div>


                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            Etiqueta población
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="pagare_etiqueta_poblacion"
                                                            class="form-control"
                                                            value="{{ $valor('pagare_etiqueta_poblacion', 'Población:') }}"
                                                        >
                                                    </div>


                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            Etiqueta teléfono
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="pagare_etiqueta_telefono"
                                                            class="form-control"
                                                            value="{{ $valor('pagare_etiqueta_telefono', 'Tel:') }}"
                                                        >
                                                    </div>

                                                </div>

                                            </div>

                                        </div>



                                        {{-- =========================================
                                            4. FIRMA
                                        ========================================== --}}
                                        <div
                                            class="tab-pane fade"
                                            id="pagare-firma"
                                        >

                                            <div class="editor-section">

                                                <div class="editor-section-title">
                                                    <span>4</span>
                                                    Pie y firmas
                                                </div>

                                                <div class="row g-3">

                                                    <div class="col-md-6">

                                                        <label class="form-label">
                                                            Texto aceptación
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="pagare_texto_acepto"
                                                            class="form-control"
                                                            value="{{ $valor('pagare_texto_acepto', 'Acepto(amos)') }}"
                                                        >

                                                    </div>


                                                    <div class="col-md-6">

                                                        <label class="form-label">
                                                            Texto firma
                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="pagare_texto_firma"
                                                            class="form-control"
                                                            value="{{ $valor('pagare_texto_firma', 'Firma(s)') }}"
                                                        >

                                                    </div>

                                                </div>

                                            </div>

                                        </div>



                                        {{-- =========================================
                                            5. APARIENCIA
                                        ========================================== --}}
                                        <div
                                            class="tab-pane fade"
                                            id="pagare-apariencia"
                                        >

                                            <div class="editor-section">

                                                <div class="editor-section-title">
                                                    <span>5</span>
                                                    Apariencia
                                                </div>

                                                <div class="row g-3">

                                                    <div class="col-md-4">

                                                        <label class="form-label">
                                                            Color principal
                                                        </label>

                                                        <input
                                                            type="color"
                                                            name="pagare_color_principal"
                                                            class="form-control form-control-color"
                                                            value="{{ $valor(
                                                                'pagare_color_principal',
                                                                '#2e7d32'
                                                            ) }}"
                                                        >

                                                    </div>


                                                    <div class="col-md-4">

                                                        <label class="form-label">
                                                            Fondo
                                                        </label>

                                                        <input
                                                            type="color"
                                                            name="pagare_color_fondo"
                                                            class="form-control form-control-color"
                                                            value="{{ $valor(
                                                                'pagare_color_fondo',
                                                                '#e8f5e9'
                                                            ) }}"
                                                        >

                                                    </div>


                                                    <div class="col-md-4">

                                                        <label class="form-label">
                                                            Tamaño texto
                                                        </label>

                                                        <div class="input-group">

                                                            <input
                                                                type="number"
                                                                name="pagare_tamano_texto"
                                                                min="8"
                                                                max="16"
                                                                step="1"
                                                                class="form-control"
                                                                value="{{ $valor(
                                                                    'pagare_tamano_texto',
                                                                    '11'
                                                                ) }}"
                                                            >

                                                            <span class="input-group-text">
                                                                px
                                                            </span>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>



                                {{-- =================================================
                                    COLUMNA DERECHA
                                ================================================== --}}
                                <div class="col-12 col-xl-4">

                                    {{-- VARIABLES EXCLUSIVAS --}}
                                    <div class="pagare-variable-card">

                                        <div class="fw-bold mb-2">
                                            <i class="bi bi-braces me-1"></i>
                                            Variables del pagaré
                                        </div>

                                        <small class="text-secondary d-block mb-3">
                                            Estas variables solo pertenecen al pagaré.
                                        </small>


                                        <div class="d-flex flex-wrap gap-2">

                                            @foreach([
                                                '{cliente}',
                                                '{folio}',

                                                '{empresa}',
                                                '{dueno_empresa}',

                                                '{monto_total}',
                                                '{monto_total_letras}',

                                                '{numero_pagare}',

                                                '{fecha_inicio}',
                                                '{fecha_fin}',
                                                '{fecha_pago}',
                                                '{fecha_expedicion}',

                                                '{lugar_pago}',
                                                '{lugar_expedicion}',

                                                '{direccion_cliente}',
                                                '{ciudad_cliente}',
                                                '{telefono_cliente}',

                                                '{dia_expedicion}',
                                                '{mes_expedicion}',
                                                '{anio_expedicion}'
                                            ] as $variable)

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-variable pagare-variable"
                                                    data-variable="{{ $variable }}"
                                                >
                                                    {{ $variable }}
                                                </button>

                                            @endforeach

                                        </div>

                                    </div>

                                </div>

                            </div>



                            {{-- ============================================
                                BOTÓN GUARDAR
                            ============================================= --}}
                            <div class="d-flex justify-content-end mt-4 pt-3 border-top">

                                <button
                                    type="submit"
                                    class="btn btn-success px-4 fw-bold"
                                >

                                    <i class="bi bi-cloud-check me-2"></i>
                                    Guardar Plantilla del Pagaré

                                </button>

                            </div>

                        </form>


                    {{-- =====================================================
                        CONTRATO - CONSERVAR EDITOR SIMPLE
                    ====================================================== --}}
                    @else

                        <div class="card border p-3 p-md-4 rounded-4 bg-body-tertiary mb-4">

                            <form
                                action="{{ route('configuracion.plantilla.update', $p->id) }}"
                                method="POST"
                            >

                                @csrf
                                @method('PUT')


                                <div class="mb-3">

                                    <label class="form-label small fw-bold text-primary text-uppercase">

                                        <i class="bi bi-card-text me-1"></i>

                                        Tipo:
                                        {{ str_replace('_', ' ', $p->tipo) }}

                                    </label>


                                    <input
                                        type="text"
                                        name="titulo"
                                        class="form-control bg-body fw-semibold"
                                        value="{{ $p->titulo }}"
                                    >

                                </div>


                                <div class="mb-3">

                                    <label class="form-label">
                                        Cláusulas Editables
                                    </label>

                                    <textarea
                                        name="contenido"
                                        class="form-control font-monospace"
                                        rows="12"
                                        required
                                    >{{ $p->contenido }}</textarea>

                                </div>


                                {{-- VARIABLES SOLO DEL CONTRATO --}}
                                <div class="p-3 bg-body border rounded-3 mb-3">

                                    <span class="d-block fw-bold text-secondary mb-2">
                                        Variables del contrato
                                    </span>


                                    <div class="d-flex flex-wrap gap-2">

                                        @foreach([
                                            '{cliente}',
                                            '{folio}',
                                            '{deposito}',
                                            '{monto_total}',
                                            '{fecha_inicio}',
                                            '{fecha_fin}',
                                            '{empresa}',
                                            '{dueno_empresa}'
                                        ] as $variable)

                                            <button
                                                type="button"
                                                class="btn btn-sm rounded-pill btn-variable font-monospace"
                                                onclick="insertVariable(this, '{{ $variable }}')"
                                            >
                                                {{ $variable }}
                                            </button>

                                        @endforeach

                                    </div>

                                </div>


                                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                    <button
                                        type="submit"
                                        class="btn btn-success px-4 fw-bold"
                                    >
                                        <i class="bi bi-cloud-check me-2"></i>
                                        Actualizar Plantilla Contrato
                                    </button>
                                </div>

                            </form>

                        </div>

                    @endif

                @endforeach

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
                        <label class="form-label small fw-semibold text-body">Contraseña<span class="text-danger">*</span></label>
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

            // =====================================================
            // PESTAÑAS PRINCIPALES DE CONFIGURACIÓN
            // =====================================================

            let activeTab =
                "{{ session('tab') }}"
                || localStorage.getItem('activeConfigTab');

            if (activeTab) {

                let tabTrigger =
                    document.querySelector(`#${activeTab}-tab`);

                if (tabTrigger) {

                    // SOLO botones principales
                    document
                        .querySelectorAll('#configTabs > .nav-item > .nav-link')
                        .forEach(function (btn) {
                            btn.classList.remove('active');
                        });


                    // SOLO paneles principales.
                    // IMPORTANTE: NO TOCAR LOS TABS INTERNOS DEL PAGARÉ
                    document
                        .querySelectorAll(
                            '#configTabsContent > .tab-pane'
                        )
                        .forEach(function (pane) {

                            pane.classList.remove(
                                'show',
                                'active'
                            );

                        });


                    tabTrigger.classList.add('active');


                    const targetSelector =
                        tabTrigger.getAttribute(
                            'data-bs-target'
                        );

                    const targetPane =
                        document.querySelector(
                            targetSelector
                        );

                    if (targetPane) {

                        targetPane.classList.add(
                            'show',
                            'active'
                        );

                    }
                }
            }


            // =====================================================
            // GUARDAR PESTAÑA PRINCIPAL
            // =====================================================

            document
                .querySelectorAll('#configTabs button')
                .forEach(function (button) {

                    button.addEventListener(
                        'shown.bs.tab',
                        function (e) {

                            let id =
                                e.target.id.replace(
                                    '-tab',
                                    ''
                                );

                            localStorage.setItem(
                                'activeConfigTab',
                                id
                            );

                        }
                    );

                });


            // =====================================================
            // GARANTIZAR ENCABEZADO DEL PAGARÉ
            // =====================================================

            function mostrarEncabezadoPagare() {

                const boton =
                    document.getElementById(
                        'pagare-encabezado-tab'
                    );

                const panel =
                    document.getElementById(
                        'pagare-encabezado'
                    );

                if (!boton || !panel) {
                    return;
                }


                // Quitar activo solamente a tabs INTERNOS del pagaré
                document
                    .querySelectorAll(
                        '#pagareEditorTabs .nav-link'
                    )
                    .forEach(function (tab) {

                        tab.classList.remove('active');

                        tab.setAttribute(
                            'aria-selected',
                            'false'
                        );

                    });


                // Ocultar solamente contenidos INTERNOS del pagaré
                const contenedor =
                    panel.closest('.tab-content');

                if (contenedor) {

                    Array.from(
                        contenedor.children
                    ).forEach(function (pane) {

                        if (
                            pane.classList.contains(
                                'tab-pane'
                            )
                        ) {
                            pane.classList.remove(
                                'show',
                                'active'
                            );
                        }

                    });

                }


                // Activar Encabezado
                boton.classList.add('active');

                boton.setAttribute(
                    'aria-selected',
                    'true'
                );

                panel.classList.add(
                    'show',
                    'active'
                );
            }


            // Si Plantillas ya viene abierta
            const panelPlantillas =
                document.getElementById(
                    'panel-plantillas'
                );

            if (
                panelPlantillas &&
                panelPlantillas.classList.contains(
                    'active'
                )
            ) {

                mostrarEncabezadoPagare();

            }


            // Cuando el usuario abra Plantillas
            const plantillasTab =
                document.getElementById(
                    'plantillas-tab'
                );

            if (plantillasTab) {

                plantillasTab.addEventListener(
                    'shown.bs.tab',
                    function () {

                        mostrarEncabezadoPagare();

                    }
                );

            }

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

        document.addEventListener('DOMContentLoaded', function () {

        // =====================================================
        // SINCRONIZAR TÍTULO DE PLANTILLA
        // =====================================================

        const tituloPagare = document.getElementById('pagare_titulo');
        const tituloPlantilla = document.getElementById('plantillaTituloPagare');

        if (tituloPagare && tituloPlantilla) {

            tituloPagare.addEventListener('input', function () {

                tituloPlantilla.value =
                    this.value.trim() !== ''
                        ? this.value
                        : 'PAGARÉ';

            });

        }

        // =====================================================
        // INSERTAR VARIABLES DEL PAGARÉ EN CUALQUIER CAMPO
        // =====================================================

        let ultimoCampoPagare = null;

        const formPagare = document.getElementById('formPagare');

        if (formPagare) {

            // Detectar cualquier campo editable del pagaré
            formPagare
                .querySelectorAll(
                    'input[type="text"], textarea'
                )
                .forEach(function (campo) {

                    campo.addEventListener('focus', function () {

                        ultimoCampoPagare = this;

                        // Marcamos visualmente el campo seleccionado
                        formPagare
                            .querySelectorAll('.campo-variable-activo')
                            .forEach(function (el) {
                                el.classList.remove(
                                    'campo-variable-activo'
                                );
                            });

                        this.classList.add(
                            'campo-variable-activo'
                        );

                    });

                    // También por clic, por si Bootstrap no dispara focus como esperamos
                    campo.addEventListener('click', function () {
                        ultimoCampoPagare = this;
                    });

                });


            // Botones de variables
            formPagare
                .querySelectorAll('.pagare-variable')
                .forEach(function (boton) {

                    boton.addEventListener(
                        'click',
                        function () {

                            const variable =
                                this.dataset.variable;

                            if (!ultimoCampoPagare) {

                                alert(
                                    'Primero selecciona el campo donde quieres colocar la variable.'
                                );

                                return;
                            }


                            const campo =
                                ultimoCampoPagare;


                            const inicio =
                                typeof campo.selectionStart === 'number'
                                    ? campo.selectionStart
                                    : campo.value.length;


                            const fin =
                                typeof campo.selectionEnd === 'number'
                                    ? campo.selectionEnd
                                    : campo.value.length;


                            const antes =
                                campo.value.substring(
                                    0,
                                    inicio
                                );

                            const despues =
                                campo.value.substring(
                                    fin
                                );


                            campo.value =
                                antes +
                                variable +
                                despues;


                            const nuevaPosicion =
                                inicio +
                                variable.length;


                            campo.focus();


                            if (
                                typeof campo.setSelectionRange ===
                                'function'
                            ) {

                                campo.setSelectionRange(
                                    nuevaPosicion,
                                    nuevaPosicion
                                );

                            }


                            // Disparar input para actualizar preview
                            campo.dispatchEvent(
                                new Event(
                                    'input',
                                    {
                                        bubbles: true
                                    }
                                )
                            );

                        }
                    );

                });

}

    });

    function previewImageGlobal(input, imgId, iconId) {
        const preview = document.getElementById(imgId);
        const icon = document.getElementById(iconId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none'); // Mostramos la imagen
                if(icon) {
                    icon.classList.add('d-none'); // Ocultamos el icono genérico
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection