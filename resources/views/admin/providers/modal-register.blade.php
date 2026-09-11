<div class="modal fade" id="modalProvider" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="providerModalTitle">
                    Registrar Proveedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formProvider" autocomplete="off" onsubmit="event.preventDefault()">
                    @csrf
                    <input type="hidden" name="id" id="provider_id">

                    <div class="row gx-3">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Tipo Documento de Identidad <span class="text-danger">*</span></label>
                                <select name="tipo_documento" id="provider_tipo_documento" class="form-select select2-provider">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($typeDocuments as $typeDocument)
                                        <option value="{{ $typeDocument->id }}" data-code="{{ $typeDocument->codigo }}">{{ $typeDocument->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">N&uacute;mero <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="dni_ruc" id="provider_dni_ruc" placeholder="Ingrese el documento">
                                    <button class="btn btn-info btn-search-provider-doc d-none" type="button">
                                        <span class="provider-search-text">Consultar</span>
                                        <span class="spinner-border spinner-border-sm d-none provider-search-spinner"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre o Raz&oacute;n Social <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="razon_social" id="provider_razon_social" placeholder="Ej: Distribuidora ABC SAC">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Direcci&oacute;n <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="direccion" id="provider_direccion" placeholder="Ej: Av. Industrial 450">
                    </div>

                    <div class="row gx-3">
                        <div class="col-lg-4" id="provider_department_wrapper">
                            <div class="mb-3">
                                <label class="form-label">Departamento</label>
                                <select name="departamento" id="provider_departamento" class="form-select select2-provider"></select>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none" id="provider_province_wrapper">
                            <div class="mb-3">
                                <label class="form-label">Provincia</label>
                                <select name="provincia" id="provider_provincia" class="form-select select2-provider"></select>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none" id="provider_district_wrapper">
                            <div class="mb-3">
                                <label class="form-label">Distrito</label>
                                <select name="distrito" id="provider_distrito" class="form-select select2-provider"></select>
                            </div>
                        </div>
                    </div>

                    <div class="row gx-3">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Tel&eacute;fono</label>
                                <input type="text" class="form-control" name="telefono" id="provider_telefono" placeholder="Opcional">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Correo Electr&oacute;nico</label>
                                <input type="email" class="form-control" name="email" id="provider_email" placeholder="Opcional">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4 btn-save-provider">Guardar</button>
            </div>
        </div>
    </div>
</div>

<style>
    #modalProvider .input-group {
        flex-wrap: nowrap;
    }

    #modalProvider .input-group > .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #modalProvider .input-group > .btn.btn-search-provider-doc {
        min-width: 78px;
        padding-inline: 16px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
</style>
