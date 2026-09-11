<div class="modal fade" id="modalAddSerie" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <form id="form_save" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title" id="modalAddSerieTitle">Registrar Serie</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mt-3 mb-3">
                        <label for="serie">Serie</label>
                        <input type="text" id="serie" class="form-control text-uppercase" name="serie" placeholder="Ejem. F001" />
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 mt-3 mb-3">
                        <label for="correlativo">Correlativo</label>
                        <input type="text" id="correlativo" class="form-control text-uppercase" name="correlativo"
                            placeholder="Ejem. 00000001" />
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 mt-3 mb-3">
                        <label for="tipo_documento">Tipo de Comprobante</label>
                        <select name="tipo_documento" id="tipo_documento" class="form-control w-100">
                            @foreach ($type_documents as $type_document)
                                <option value="{{ $type_document->id }}">{{ $type_document->descripcion }} </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mt-3 mb-3">
                        <label for="idcaja">Caja</label>
                        <select name="idcaja" id="idcaja" class="form-control w-100">
                            @foreach ($cashes as $cash)
                                <option value="{{ $cash->id }}">{{ $cash->descripcion }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-success btn-save">
                    <span class="text-save">Guardar cambios</span>
                    <span class="me-1 d-none text-saving" role="status" aria-hidden="true">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <span class="text-saving d-none">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditSerie" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <form id="form_edit" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title" id="modalEditSerieTitle">Actualizar Serie</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mt-3 mb-3">
                        <label for="serie">Serie</label>
                        <input type="hidden" name="id">
                        <input type="text" id="serie" class="form-control text-uppercase" name="serie" placeholder="Ejem. F001" />
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 mt-3 mb-3">
                        <label for="correlativo">Correlativo</label>
                        <input type="text" id="correlativo" class="form-control text-uppercase" name="correlativo"
                        placeholder="Ejem. 00000001" />
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 mt-3 mb-3">
                        <label for="tipo_documento">Tipo de Comprobante</label>
                        <select name="tipo_documento" id="tipo_documento" class="form-control w-100">
                            @foreach ($type_documents as $type_document)
                                <option value="{{ $type_document->id }}">{{ $type_document->descripcion }} </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mt-3 mb-3">
                        <label for="idcaja">Caja</label>
                        <select name="idcaja" id="idcaja" class="form-control w-100">
                            @foreach ($cashes as $cash)
                                <option value="{{ $cash->id }}">{{ $cash->descripcion }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-success btn-store">
                    <span class="text-store">Guardar cambios</span>
                    <span class="me-1 d-none text-storing" role="status">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <span class="text-storing d-none">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>