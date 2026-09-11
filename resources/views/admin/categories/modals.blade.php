<!-- Modal para Editar Categor&iacute;a -->
<div class="modal fade" id="modalEditCategory" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <form id="form_edit" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title" id="modalEditCategoryTitle">Actualizar Categor&iacute;a</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mt-3 mb-3">
                        <input type="hidden" name="id">
                        <label for="descripcion">Descripci&oacute;n</label>
                        <input type="text" id="descripcion" class="form-control text-uppercase" name="descripcion" />
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-success btn-store">
                    <span class="text-store">Guardar cambios</span>
                    <span class="me-1 d-none text-storing" role="status" aria-hidden="true">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <span class="text-storing d-none">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
