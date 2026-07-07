@extends('layouts.admin')

@section('content')
<style>
    /* Estética SaaS Minimalista Premium */
    body { background-color: #f8fafc; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    
    .page-header { margin-bottom: 32px; }
    .page-title { font-size: 22px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
    .page-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }

    /* Barra de Navegación de Pestañas Planas (UX Limpio) */
    .premium-tabs { border-bottom: 1px solid #e2e8f0; gap: 28px; margin-bottom: 28px; }
    .premium-tabs .nav-link { color: #64748b; font-weight: 600; font-size: 14px; padding: 12px 4px; border: none; background: transparent; border-bottom: 2px solid transparent; border-radius: 0; transition: all 0.2s ease; }
    .premium-tabs .nav-link.active { color: #0d6efd; border-bottom-color: #0d6efd; font-weight: 700; }
    .premium-tabs .nav-link:hover:not(.active) { color: #1e293b; }

    /* Paneles de Contenedor Blanco */
    .panel-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01); padding: 28px; margin-bottom: 24px; }
    .panel-title-area { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; }
    .panel-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }

    /* Tarjetas de Sucursales */
    .branch-card { display: flex; align-items: center; justify-content: space-between; padding: 18px; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 14px; background: #ffffff; transition: border-color 0.2s ease; }
    .branch-card:hover { border-color: #cbd5e1; }
    .branch-meta { display: flex; align-items: center; gap: 16px; }
    .media-frame { width: 46px; height: 44px; border-radius: 50%; background: #f1f5f9; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .media-frame img { width: 100%; height: 100%; object-fit: cover; }
    
    /* Tabla Profesional de Usuarios */
    .user-table-wrapper { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #ffffff; }
    .user-table { width: 100%; border-collapse: collapse; margin: 0; }
    .user-table th { background: #f8fafc; color: #475569; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; }
    .user-table td { padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #334155; vertical-align: middle; }
    .user-table tr:last-child td { border-bottom: none; }
    
    /* Estados y Badges */
    .badge-status { padding: 4px 8px; font-size: 11px; font-weight: 600; border-radius: 6px; display: inline-block; }
    .badge-status.active { background: #ecfdf5; color: #065f46; }
    .badge-status.inactive { background: #fef2f2; color: #991b1b; }
    
    /* Formulario */
    .form-label { font-size: 12px; font-weight: 600; color: #344054; margin-bottom: 6px; }
    .form-control, .form-select { border-radius: 8px; padding: 10px 14px; border: 1px solid #d0d5dd; font-size: 13px; color: #1e2939; background-color: #ffffff; }
    .form-control:focus, .form-select:focus { border-color: #0d6efd; box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08); }

    /* Modales Minimalistas */
    .modal-content { border-radius: 14px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 20px 24px; }
    .modal-title { font-size: 16px; font-weight: 700; color: #0f172a; }
    .modal-footer { border-top: 1px solid #f1f5f9; padding: 16px 24px; background: #f8fafc; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px; }
    
    .btn { font-size: 13px; font-weight: 600; padding: 9px 18px; border-radius: 8px; transition: all 0.2s; }
    .btn-primary { background: #0d6efd; border: none; color: white; }
    .btn-primary:hover { background: #0b5ed7; }
    .btn-action-outline { background: #ffffff; border: 1px solid #e2e8f0; color: #475569; padding: 6px 12px; border-radius: 6px; }
    .btn-action-outline:hover { background: #f8fafc; color: #0f172a; }
</style>

<div class="container-fluid py-2">
    <div class="page-header">
        <h1 class="page-title"><i class="bi bi-shield-gear text-primary me-2"></i>Consola de Configuración Corporativa</h1>
        <p class="page-subtitle">Administra los datos globales de tu empresa, gestiona sucursales y controla los perfiles de acceso de tus empleados.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <ul class="nav premium-tabs" id="configTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="empresa-tab" data-bs-toggle="tab" data-bs-target="#panel-empresa" type="button" role="tab">
                <i class="bi bi-building me-2"></i>Datos de la Empresa
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="sucursales-tab" data-bs-toggle="tab" data-bs-target="#panel-sucursales" type="button" role="tab">
                <i class="bi bi-geo-alt me-2"></i>Sucursales y Tiendas
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#panel-usuarios" type="button" role="tab">
                <i class="bi bi-people me-2"></i>Usuarios y Operadores
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="plantillas-tab" data-bs-toggle="tab" data-bs-target="#panel-plantillas" type="button" role="tab">
                <i class="bi bi-file-earmark-richtext me-2"></i>Plantillas de Documentos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="configTabsContent">
        
        <!-- PANELES DE EMPRESA -->
        <div class="tab-pane fade show active" id="panel-empresa" role="tabpanel">
            <div class="panel-box bg-white p-4 rounded-4 shadow-sm">
                <h5 class="panel-title mb-4 fw-bold text-dark">Información de la Entidad Legal y Marca</h5>
                
                <form action="{{ route('configuracion.empresa.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-3 text-center border-end">
                            <label class="form-label d-block fw-semibold text-muted mb-3">Logo General del Sistema</label>
                            <div class="mb-3 d-flex justify-content-center align-items-center bg-light rounded-4 mx-auto" style="width: 160px; height: 160px; overflow: hidden; border: 2px dashed #cbd5e1;">
                                @if(\App\Helpers\ContentHelper::getCompanyData('empresa_logo'))
                                    <img id="logo-preview" src="{{ asset('storage/' . \App\Helpers\ContentHelper::getCompanyData('empresa_logo')) }}" class="img-fluid h-100 w-100 object-fit-cover">
                                @else
                                    <img id="logo-preview" src="" class="img-fluid h-100 w-100 object-fit-cover d-none">
                                    <i id="logo-placeholder" class="bi bi-image text-muted fs-1"></i>
                                @endif
                            </div>
                            <div class="input-group input-group-sm">
                                <input type="file" name="empresa_logo" id="empresa_logo" class="form-control" accept="image/*">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Formatos recomendados: PNG, JPG (Max 2MB)</small>
                        </div>

                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label required-label fw-semibold">Nombre de la Empresa o Razón Social</label>
                                    <input type="text" name="empresa_nombre" class="form-control" placeholder="Ej. Corporativo Comercializador S.A." value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_nombre') }}" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">RFC / Identificación Fiscal</label>
                                    <input type="text" name="empresa_rfc" class="form-control" placeholder="Ej. ABC123456XYZ" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_rfc') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Dirección Fiscal / Matriz</label>
                                    <input type="text" name="empresa_direccion" class="form-control" placeholder="Calle, Número, Colonia, C.P." value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_direccion') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Teléfono Corporativo</label>
                                    <input type="text" name="empresa_telefono" class="form-control" placeholder="Ej. (618) 123-4567" value="{{ \App\Helpers\ContentHelper::getCompanyData('empresa_telefono') }}">
                                </div>
                            </div>

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3 shadow-sm">
                                    <i class="bi bi-cloud-check me-2"></i>Guardar Configuración Corporativa
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- PANELES DE SUCURSALES -->
        <div class="tab-pane fade" id="panel-sucursales" role="tabpanel">
            <div class="panel-box bg-white p-4 rounded-4 shadow-sm">
                <div class="panel-title-area d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="panel-title fw-bold text-dark m-0">Unidades de Negocio y Sucursales</h5>
                    <button class="btn btn-primary shadow-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearSucursal">
                        <i class="bi bi-plus-lg me-1"></i> Registrar Nueva Sucursal
                    </button>
                </div>

                <div class="branch-container">
                    @forelse($sucursales as $suc)
                        <div class="branch-card d-flex align-items-center justify-content-between p-3 border rounded-3 mb-3 bg-white shadow-sm">
                            <div class="branch-meta d-flex align-items-center gap-3">
                                <div class="media-frame rounded-circle bg-light border d-flex align-items-center justify-content-center overflow-hidden" style="width: 50px; height: 50px;">
                                    @if($suc->logo)
                                        <img src="{{ asset('storage/' . $suc->logo) }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <i class="bi bi-geo-alt text-muted fs-4"></i>
                                    @endif
                                </div>
                                <div>
                                    <h6 class="branch-name fw-bold mb-1 text-dark">{{ $suc->nombre }}</h6>
                                    <p class="branch-desc text-muted small mb-0 d-flex gap-3">
                                        <span><i class="bi bi-pin-map text-primary me-1"></i>{{ $suc->direccion }}</span>
                                        @if($suc->telefono)
                                            <span><i class="bi bi-telephone text-secondary me-1"></i>{{ $suc->telefono }}</span>
                                        @endif
                                        @if($suc->rfc)
                                            <span><i class="bi bi-hash text-dark me-1"></i>{{ $suc->rfc }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge rounded-pill px-3 py-2 {{ $suc->activa ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}" style="font-size: 11px; font-weight: 600;">
                                    {{ $suc->activa ? 'Operativa' : 'Suspendida' }}
                                </span>
                                <button class="btn btn-action-outline btn-sm border rounded-3 fw-semibold text-secondary" data-bs-toggle="modal" data-bs-target="#modalEditarSucursal{{ $suc->id }}">
                                    <i class="bi bi-pencil me-1"></i> Configurar
                                </button>
                            </div>
                        </div>

                        <div class="modal fade" id="modalEditarSucursal{{ $suc->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header bg-dark text-white border-0 py-3">
                                        <h5 class="modal-title fw-bold"><i class="bi bi-building-gear me-2"></i>Actualizar Sucursal</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('configuracion.sucursal.update', $suc->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <div class="modal-body p-4 bg-light">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold small text-muted">Nombre de la Sucursal <span class="text-danger">*</span></label>
                                                <input type="text" name="nombre" class="form-control" value="{{ $suc->nombre }}" required>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold small text-muted">RFC de Facturación</label>
                                                    <input type="text" name="rfc" class="form-control" value="{{ $suc->rfc }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold small text-muted">Teléfono de Atención</label>
                                                    <input type="text" name="telefono" class="form-control" value="{{ $suc->telefono }}">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold small text-muted">Dirección Completa <span class="text-danger">*</span></label>
                                                <input type="text" name="direccion" class="form-control" value="{{ $suc->direccion }}" required>
                                            </div>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-8">
                                                    <label class="form-label fw-semibold small text-muted">Cambiar Imagen/Logo</label>
                                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label fw-semibold small text-muted">Estado</label>
                                                    <select name="activa" class="form-select">
                                                        <option value="1" {{ $suc->activa ? 'selected' : '' }}>Operativa</option>
                                                        <option value="0" {{ !$suc->activa ? 'selected' : '' }}>Suspendida</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 p-3 bg-white">
                                            <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary px-4 fw-bold">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 border border-dashed rounded-4 bg-light">
                            <div class="bg-secondary bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3 text-secondary">
                                <i class="bi bi-geo fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark">No hay sucursales registradas</h5>
                            <p class="text-muted small mx-auto" style="max-width: 360px;">Registra tu primera sucursal física o almacén para poder enlazar inventarios y cajeros operadores.</p>
                            <button class="btn btn-sm btn-primary fw-bold px-3 rounded-3 mt-2" data-bs-toggle="modal" data-bs-target="#modalCrearSucursal">
                                <i class="bi bi-plus-lg me-1"></i> Configurar ahora
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- PANELES DE USUARIOS -->
        <div class="tab-pane fade" id="panel-usuarios" role="tabpanel">
            <div class="panel-box bg-white p-4 rounded-4 shadow-sm">
                <div class="panel-title-area d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="panel-title fw-bold text-dark m-0">Operadores de Caja e Inventario Autorizados</h5>
                    <button class="btn btn-dark shadow-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                        <i class="bi bi-person-plus-fill me-1"></i> Registrar Empleado
                    </button>
                </div>

                <div class="user-table-wrapper border rounded-3 overflow-hidden shadow-sm">
                    <table class="user-table table m-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3 text-secondary small fw-bold text-uppercase" style="font-size: 11px;">Nombre Operador</th>
                                <th class="text-secondary small fw-bold text-uppercase" style="font-size: 11px;">Email Corporativo</th>
                                <th class="text-secondary small fw-bold text-uppercase" style="font-size: 11px;">Sucursal Laboral</th>
                                <th class="text-secondary small fw-bold text-uppercase" style="font-size: 11px;">Rol Sistema</th>
                                <th class="text-secondary small fw-bold text-uppercase" style="font-size: 11px;">Estado</th>
                                <th class="text-end pe-3 text-secondary small fw-bold text-uppercase" style="font-size: 11px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $user)
                            <tr>
                                <td class="ps-3 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center border overflow-hidden" style="width: 38px; height: 38px; font-size: 14px;">
                                            @if($user->foto)
                                                <img src="{{ asset('storage/' . $user->foto) }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block" style="font-size: 13.5px;">{{ $user->name }}</span>
                                            <small class="text-muted" style="font-size: 11px;">ID: #{{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $user->email }}</td>
                                <td>
                                    <span class="fw-semibold text-dark small">
                                        <i class="bi bi-geo-alt text-muted me-1"></i>{{ $user->sucursal->nombre ?? 'Sin asignar' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1.5 font-sans fw-bold text-uppercase" style="font-size: 10.5px;">Administrador</span>
                                    @elseif($user->role == 'gerente')
                                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2.5 py-1.5 font-sans fw-bold text-uppercase" style="font-size: 10.5px;">Gerente</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2.5 py-1.5 font-sans fw-bold text-uppercase" style="font-size: 10.5px;">Cajero / POS</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $user->status == 'activo' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 11px;">
                                        {{ $user->status == 'activo' ? 'Activo' : 'Inhabilitado' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group shadow-sm border rounded-3 bg-white overflow-hidden">
                                        <button class="btn btn-light btn-sm border-0 bg-white" title="Cambiar Contraseña" data-bs-toggle="modal" data-bs-target="#modalPassword{{ $user->id }}">
                                            <i class="bi bi-key-fill text-muted"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm border-0 border-start bg-white" title="Editar Operador" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario{{ $user->id }}">
                                            <i class="bi bi-pencil-square text-muted"></i>
                                        </button>
                                        
                                        @if($user->status == 'activo')
                                            <form action="{{ route('configuracion.usuarios.baja', $user->id) }}" method="POST" class="d-inline m-0">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-light btn-sm border-0 border-start bg-white text-danger" title="Dar de Baja" onclick="return confirm('¿Suspender accesos al sistema para este operador?')">
                                                    <i class="bi bi-person-x-fill"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('configuracion.usuarios.alta', $user->id) }}" method="POST" class="d-inline m-0">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-light btn-sm border-0 border-start bg-white text-success" title="Reactivar Operador">
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
                                    <p class="text-muted small mb-0">Solo tú tienes acceso al sistema actualmente.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 🛠️ PANELES DE PLANTILLAS (CORREGIDO: ADENTRO DEL CONTENEDOR Y RESPONSIVO) -->
        <div class="tab-pane fade" id="panel-plantillas" role="tabpanel">
            <div class="panel-box bg-white p-4 rounded-4 shadow-sm">
                <div class="panel-title-area d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="panel-title fw-bold text-dark m-0">Estructura Legal de Documentos Imprimibles</h5>
                </div>
                
                <div class="row g-4">
                    @forelse($plantillas as $p)
                    <div class="col-xl-6 col-lg-12">
                        <div class="card border-0 shadow-sm p-4 rounded-4 bg-light h-100">
                            <form action="{{ route('configuracion.plantilla.update', $p->id) }}" method="POST" class="d-flex flex-column h-100">
                                @csrf @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-primary text-uppercase tracking-wider" style="font-size: 11px;">
                                        <i class="bi bi-file-earmark-text-fill me-1"></i> Identificador: {{ str_replace('_', ' ', $p->tipo) }}
                                    </label>
                                    <input type="text" name="titulo" class="form-control fw-semibold" value="{{ $p->titulo }}" placeholder="Título oficial del documento">
                                </div>
                                <div class="mb-3 flex-grow-1">
                                    <label class="form-label text-muted small fw-semibold">Cuerpo del Documento / Cláusulas Legales</label>
                                    <textarea name="contenido" class="form-control font-monospace small bg-white" rows="10" style="font-size: 12px; line-height: 1.5; resize: vertical;" required>{{ $p->contenido }}</textarea>
                                </div>
                                
                                <div class="p-3 bg-white border rounded-3 mb-3">
                                    <span class="d-block fw-bold text-dark mb-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="bi bi-code-slash text-secondary me-1"></i> Atajos de Variables Rápidas:
                                    </span>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="this.closest('form').querySelector('textarea').value += ' {cliente}'">{cliente}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="this.closest('form').querySelector('textarea').value += ' {folio}'">{folio}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="this.closest('form').querySelector('textarea').value += ' {monto_total}'">{monto_total}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="this.closest('form').querySelector('textarea').value += ' {monto_neto}'">{monto_neto}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="this.closest('form').querySelector('textarea').value += ' {deposito}'">{deposito}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="this.closest('form').querySelector('textarea').value += ' {fecha_fin}'">{fecha_fin}</button>
                                        <button type="button" class="badge btn btn-light border text-dark font-monospace" style="font-size: 11px;" onclick="this.closest('form').querySelector('textarea').value += ' {empresa}'">{empresa}</button>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 11px; line-height: 1.3;"><i class="bi bi-info-circle me-1"></i> Haz clic en los botones superiores para insertar variables automáticamente al texto.</small>
                                </div>
                                
                                <button type="submit" class="btn btn-dark w-100 py-2 fw-bold shadow-sm mt-auto">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Guardar Cambios de Formato
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5 border border-dashed rounded-4 bg-light">
                        <i class="bi bi-file-earmark-richtext text-muted fs-1 mb-2"></i>
                        <h6 class="fw-bold text-dark">No hay plantillas base inicializadas</h6>
                        <p class="text-muted small mb-0">Ejecuta el seeder <code>PlantillasDocumentosSeeder</code> desde tu terminal para cargar los esquemas iniciales.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL CREAR SUCURSAL -->
<div class="modal fade" id="modalCrearSucursal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-building-add me-2"></i>Registrar Nueva Unidad de Negocio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('configuracion.sucursal.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Nombre Comercial de la Sucursal <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Sucursal Norte Viramontes">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">RFC Sucursal</label>
                            <input type="text" name="rfc" class="form-control" placeholder="Opcional">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Teléfono Comercial</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Para tickets">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Dirección Geográfica Completa <span class="text-danger">*</span></label>
                        <input type="text" name="direccion" class="form-control" required placeholder="Calle, Número, Colonia, C.P.">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small text-muted">Logo de la Sucursal</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Registrar Sucursal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CREAR USUARIO -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Alta de Operador de Sistema</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('configuracion.usuarios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Nombre Completo del Empleado <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Nombre del empleado">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Correo Electrónico Corp. <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="empleado@correo.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Contraseña Base <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Rol Autorizado <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="cajero" selected>Cajero / POS</option>
                                <option value="gerente">Gerente de Sucursal</option>
                                <option value="admin">Administrador Global</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Vincular a Sucursal <span class="text-danger">*</span></label>
                            <select name="sucursal_id" class="form-select" required>
                                <option value="" disabled selected>Seleccione...</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Fotografía de Perfil (Opcional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Guardar y Enlazar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                let previewContainer = this.closest('.row, .modal-body').querySelector('img');
                if(previewContainer) {
                    reader.onload = function(e) {
                        previewContainer.src = e.target.result;
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });

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