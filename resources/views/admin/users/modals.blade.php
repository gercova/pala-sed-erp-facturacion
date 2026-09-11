<div class="modal fade" id="modalAddUser" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form_save" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Registrar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control text-uppercase" name="nombres">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control text-lowercase" name="user">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role">
                            <option value="">[SELECCIONE]</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="1" selected>Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Caja</label>
                        <select class="form-select" name="idcaja">
                            @foreach ($cashes as $cash)
                                <option value="{{ $cash->id }}">{{ $cash->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Almacenes asignados</label>
                        <select class="form-select" name="warehouse_ids[]" multiple>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->descripcion }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">El primer almacén seleccionado quedará como activo inicial.</small>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-save">
                            <span class="text-save">Guardar cambios</span>
                            <span class="spinner-grow spinner-grow-sm me-1 d-none text-saving" role="status" aria-hidden="true"></span>
                            <span class="text-saving d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditUser" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form_edit" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <input type="hidden" name="id">

                    <div class="col-12">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control text-uppercase" name="nombres">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control text-lowercase" name="user">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" placeholder="Solo si desea cambiarla">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role">
                            <option value="">[SELECCIONE]</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Caja</label>
                        <select class="form-select" name="idcaja">
                            @foreach ($cashes as $cash)
                                <option value="{{ $cash->id }}">{{ $cash->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Almacenes asignados</label>
                        <select class="form-select" name="warehouse_ids[]" multiple>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->descripcion }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">El primer almacén seleccionado quedará como activo inicial.</small>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-store">
                            <span class="text-store">Guardar cambios</span>
                            <span class="me-1 d-none text-storing" role="status" aria-hidden="true"><i class="fas fa-spinner fa-spin"></i></span>
                            <span class="text-storing d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalUpdateRole" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <form id="form_update_role" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Rol Rápido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="hidden" name="id">
                        <input type="text" class="form-control text-uppercase" name="usuario" disabled>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Rol</label>
                        <div id="wrapper_roles"></div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-update-role">
                            <span class="text-update-role">Guardar cambios</span>
                            <span class="me-1 text-saving-role d-none" role="status" aria-hidden="true"><i class="fas fa-spinner fa-spin"></i></span>
                            <span class="text-saving-role d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
