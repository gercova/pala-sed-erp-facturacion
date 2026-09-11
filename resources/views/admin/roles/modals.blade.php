<div class="modal fade" id="modalAddRole" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form id="form_save" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddRoleTitle">Registrar Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-12">
                        <label for="name" class="form-label">Descripcion</label>
                        <input type="text" id="name" class="form-control text-uppercase" name="name">
                        <div class="invalid-feedback">El campo no debe estar vacio.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Permisos del rol</label>
                        <div class="row g-3">
                            @foreach ($permissionGroups as $group)
                                <div class="col-md-6">
                                    <div class="card border h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1">{{ $group['label'] }}</h6>
                                                    <p class="text-muted small mb-0">{{ $group['description'] }}</p>
                                                </div>
                                            </div>
                                            @foreach ($group['permissions'] as $permission)
                                                <div class="form-check mt-3">
                                                    <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permissions[]" id="permission{{ $permission->id }}">
                                                    <label class="form-check-label" for="permission{{ $permission->id }}">
                                                        {{ $permission->descripcion ?: $permission->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-save">
                            <span class="text-save">Guardar</span>
                            <span class="me-1 d-none text-saving" role="status" aria-hidden="true"><i class="fas fa-spinner fa-spin"></i></span>
                            <span class="text-saving d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditRole" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form id="form_edit" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditRoleTitle">Actualizar Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripcion</label>
                        <input type="hidden" name="id">
                        <input type="text" id="name" class="form-control text-uppercase" name="name">
                        <div class="invalid-feedback">El campo no debe estar vacio.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Permisos del rol</label>
                        <div id="wrapper_permissions"></div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-store">
                            <span class="text-store">Guardar</span>
                            <span class="me-1 d-none text-storing" role="status" aria-hidden="true"><i class="fas fa-spinner fa-spin"></i></span>
                            <span class="text-storing d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
