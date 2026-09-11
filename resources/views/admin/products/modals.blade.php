<div class="offcanvas offcanvas-end" tabindex="-1" id="offCanvasDetail" aria-labelledby="offCanvasDetailLabel1">
    <div class="offcanvas-header border-bottom border-block-end-dashed">
        <h5 class="offcanvas-title" id="offCanvasDetailLabel1">INFORMACI&Oacute;N DE PRODUCTO</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div>
            <ul class="list-group list-group-flush mb-0">
                <li class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <p class="fw-semibold mb-0">C&oacute;digo interno</p>
                            <span class="fs-12 text-muted detail-code"></span>
                        </div>
                    </div>
                </li>

                <li class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <p class="fw-semibold mb-0">C&oacute;digo de barras</p>
                            <span class="fs-12 text-muted detail-barcode"></span>
                        </div>
                    </div>
                </li>

                <li class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <p class="fw-semibold mb-0">Descripci&oacute;n</p>
                            <span class="fs-12 text-muted detail-description"></span>
                        </div>
                    </div>
                </li>

                <li class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <p class="fw-semibold mb-0">Categor&iacute;a</p>
                            <span class="fs-12 text-muted detail-category"></span>
                        </div>
                    </div>
                </li>

                <li class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <p class="fw-semibold mb-0">Precio Compra</p>
                            <span class="fs-12 text-muted detail-buy"></span>
                        </div>
                    </div>
                </li>

                <li class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <p class="fw-semibold mb-0">Precio Venta</p>
                            <span class="fs-12 text-muted detail-sale"></span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditProduct" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="form_edit_product" class="modal-content border-0 shadow-lg" onsubmit="event.preventDefault()">
            @csrf
            <input type="hidden" name="id">
            <input type="hidden" name="original_opcion" id="edit_original_opcion">

            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold d-flex align-items-center">
                    <i class="fas fa-edit text-primary me-2"></i> Actualizar Producto o Servicio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert bg-light border-0 shadow-sm mb-4" style="border-left: 4px solid #0d6efd;">
                    <div class="d-flex">
                        <div class="me-2 text-primary">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="small text-muted">
                            <strong class="text-dark">Flujo del sistema:</strong><br>
                            Los precios y el stock se definen al registrar y luego se gestionan desde
                            <strong>almacenes</strong>. Aqu&iacute; puedes actualizar la informaci&oacute;n fiscal y general
                            del cat&aacute;logo.
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase">Nombre del Producto o Servicio</label>
                        <input type="text" name="descripcion" class="form-control form-control-lg text-uppercase border-2" placeholder="Ej: MANTENIMIENTO / SSD 500GB" required>
                        <div class="invalid-feedback">Campo obligatorio</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">C&oacute;d. Interno</label>
                        <input type="text" name="codigo_interno" class="form-control bg-light" placeholder="Opcional">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">C&oacute;d. Barras</label>
                        <input type="text" name="codigo_barras" class="form-control bg-light" placeholder="Opcional">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">C&oacute;d. SUNAT</label>
                        <input type="text" name="codigo_sunat" class="form-control bg-light" placeholder="Opcional">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Unidad</label>
                        <select name="idunidad" class="form-select border-2 edit-product-select">
                            <option value="">Seleccione...</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->descripcion }} ({{ $unit->codigo }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase text-secondary">Categor&iacute;a</label>
                        <select name="idcategoria" class="form-select border-2 edit-product-select">
                            <option value="">Seleccione categor&iacute;a...</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->descripcion }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Campo obligatorio</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Afectaci&oacute;n IGV</label>
                        <select name="idcodigo_igv" class="form-select border-2 edit-product-select" id="edit_idcodigo_igv" data-default="{{ $igvTypeAffections->firstWhere('codigo', '10')?->id ?? $igvTypeAffections->first()?->id }}">
                            <option value="">Seleccione...</option>
                            @foreach ($igvTypeAffections as $igvType)
                                <option value="{{ $igvType->id }}" data-code="{{ $igvType->codigo }}">{{ $igvType->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="text-muted opacity-25">

                <div class="row g-3 mb-4 align-items-end">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted text-uppercase d-block">Tipo de Item</label>
                        <div class="btn-group w-100 shadow-sm" role="group">
                            <input type="radio" class="btn-check" name="opcion" id="edit_producto" value="1">
                            <label class="btn btn-outline-primary py-2" for="edit_producto">
                                <i class="fas fa-boxes me-1"></i> Producto
                            </label>

                            <input type="radio" class="btn-check" name="opcion" id="edit_servicio" value="2">
                            <label class="btn btn-outline-primary py-2" for="edit_servicio">
                                <i class="fas fa-tools me-1"></i> Servicio
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4 fw-bold text-secondary" data-bs-dismiss="modal">Cancelar</button>

                <button class="btn btn-primary px-5 fw-bold shadow-sm btn-store-product" type="submit">
                    <span class="text-store-product">
                        <i class="fas fa-check-circle me-1"></i> Guardar Cambios
                    </span>
                    <span class="d-none text-storing-product">
                        <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalUpload" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form_excel" class="modal-content" onsubmit="event.preventDefault()" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalUploadTitle">Cargar Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="excel">Importar documento (.xlsx)</label>
                        <input type="file" id="excel" class="form-control" name="excel">
                        <div class="invalid-feedback">El campo no debe estar vac&iacute;o.</div>
                        <small class="form-text text-muted">Aseg&uacute;rate de que el archivo est&eacute; en formato .xlsx y cumpla con el formato requerido.</small>
                    </div>

                    <div class="col-12 mb-3 mt-2">
                        <a href="{{ route('products.download_excel') }}" class="btn btn-dark">
                            <i class="ri-download-2-line me-1"></i>
                            Descargar formato con lista actualizada
                        </a>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success btn-upload-product">
                            <span class="text-upload-product">Guardar cambios</span>
                            <span class="spinner-border spinner-border-sm me-1 d-none text-uploads-product" role="status" aria-hidden="true"></span>
                            <span class="text-uploads-product d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
