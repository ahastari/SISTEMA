<div class="modal fade" id="modalPassword{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('configuracion.usuarios.password', $user->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-key me-2"></i>Nueva Contraseña</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 bg-light">
                    <label class="form-label small text-muted">Establecer clave para: <strong>{{ $user->name }}</strong></label>
                    <input type="password" name="password" class="form-control rounded-3" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                <div class="modal-footer border-0 p-2 bg-white">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold rounded-3 py-2">Actualizar Clave</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarUsuario{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-gear me-2"></i>Modificar Perfil Operador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('configuracion.usuarios.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nombre Completo</label>
                        <input type="text" name="name" class="form-control rounded-3" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control rounded-3" value="{{ $user->email }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Rol Autorizado</label>
                            <select name="role" class="form-select rounded-3" required>
                                <option value="cajero" {{ $user->role == 'cajero' ? 'selected' : '' }}>Cajero / POS</option>
                                <option value="gerente" {{ $user->role == 'gerente' ? 'selected' : '' }}>Gerente de Sucursal</option>
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrador Global</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted">Sucursal de Trabajo</label>
                            <select name="sucursal_id" class="form-select rounded-3" required>
                                @foreach($sucursales as $sSelect)
                                    <option value="{{ $sSelect->id }}" {{ $user->sucursal_id == $sSelect->id ? 'selected' : '' }}>{{ $sSelect->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-muted">Actualizar Fotografía</label>
                        <input type="file" name="foto" class="form-control rounded-3" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white">
                    <button type="button" class="btn btn-light btn-sm rounded-3 px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold rounded-3 px-4">Actualizar Datos</button>
                </div>
            </form>
        </div>
    </div>
</div>