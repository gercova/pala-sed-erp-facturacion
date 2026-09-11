<div class="modal fade" id="modalEditClient" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form_edit_client" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditClientTitle">Actualizar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label for="nro_documento">Número de Documento</label>
                        <input type="hidden" name="id" id="id">
                        <input type="text" class="form-control" name="nro_documento">
                        <div class="invalid-feedback">El campo no debe estar vacío</div>
                    </div>

                    <div class="col-12 col-md-8 mb-3">
                        <label for="nombres">Nombre o Razón Social</label>
                        <input type="text" class="form-control text-uppercase" name="nombres">
                        <div class="invalid-feedback">El campo no debe estar vacío</div>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="direccion">Dirección</label>
                        <input type="text" class="form-control text-uppercase" name="direccion">
                        <div class="invalid-feedback">El campo no debe estar vacío</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="telefono">Teléfono <small class="text-muted">(Opcional)</small></label>
                        <input type="text" class="form-control" name="telefono">
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="email">Correo Electrónico <small class="text-muted">(Opcional)</small></label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-store-client">
                            <span class="text-store-client">Guardar cambios</span>
                            <span class="me-1 d-none text-storing-client" role="status"
                                aria-hidden="true">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                            <span class="text-storing-client d-none">Guardando...</span>
                        </button>
            </div>
        </form>
    </div>
</div>
