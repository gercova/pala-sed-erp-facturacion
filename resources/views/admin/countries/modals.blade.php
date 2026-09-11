<div class="modal fade" id="modalAddCountry" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form_save" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddCountryTitle">Registrar Pa&iacute;s</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="descripcion" class="form-label">Descripci&oacute;n</label>
                        <input type="text" id="descripcion" class="form-control text-uppercase" name="descripcion">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="prefijo" class="form-label">Prefijo</label>
                        <input type="text" id="prefijo" class="form-control text-uppercase" name="prefijo" placeholder="Ejm. PE">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="codigo_telefono" class="form-label">C&oacute;d. Tel&eacute;fono</label>
                        <input type="text" id="codigo_telefono" class="form-control text-uppercase" name="codigo_telefono" placeholder="Ejm. +51">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="signo" class="form-label">Signo</label>
                        <input type="text" id="signo" class="form-control text-uppercase" name="signo" placeholder="Ejm. S/">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="moneda" class="form-label">Moneda</label>
                        <input type="text" id="moneda" class="form-control text-uppercase" name="moneda" placeholder="Ejm. SOLES">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-primary btn-save">
                            <span class="text-save">Guardar</span>
                            <span class="me-1 d-none text-saving" role="status" aria-hidden="true">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                            <span class="text-saving d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="modalEditCountry" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form_edit" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditCountryTitle">Actualizar Pa&iacute;s</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="descripcion" class="form-label">Descripci&oacute;n</label>
                        <input type="text" id="descripcion" class="form-control text-uppercase" name="descripcion">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="prefijo" class="form-label">Prefijo</label>
                        <input type="hidden" name="id">
                        <input type="text" id="prefijo" class="form-control text-uppercase" name="prefijo">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="codigo_telefono" class="form-label">C&oacute;d. Tel&eacute;fono</label>
                        <input type="text" id="codigo_telefono" class="form-control text-uppercase" name="codigo_telefono">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="signo" class="form-label">Signo</label>
                        <input type="text" id="signo" class="form-control text-uppercase" name="signo">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="moneda" class="form-label">Moneda</label>
                        <input type="text" id="moneda" class="form-control text-uppercase" name="moneda">
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-primary btn-store">
                            <span class="text-store">Guardar</span>
                            <span class="me-1 d-none text-storing" role="status" aria-hidden="true">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                            <span class="text-storing d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>