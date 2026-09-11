<div class="modal fade" id="modalDetailProducts" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form id="form_save" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailProductsTitle">Agregar Productos al Almac&eacute;n</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-1">
                    <div class="text-end">
                        <button class="btn btn-info btn-sm btn-all-products" tabindex="0" data-idalmacen="{{ $warehouse->id }}">
                            <i class="ri-add-line align-middle"></i> Agregar todos los productos
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table_detail" class="table table-growed table-hover table-sm">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center"></th>
                                <th scope="col" class="text-center" width="12%">C&oacute;digo Barras</th>
                                <th scope="col" class="text-center" width="12%">C&oacute;digo Interno</th>
                                <th scope="col">Descripci&oacute;n</th>
                                <th scope="col" class="text-center" width="12%">Categor&iacute;a</th>
                                <th scope="col" class="text-center" width="10%">Stock Inicial</th>
                            </tr>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col"><input type="text" class="form-control form-control-sm" name="search_barcode" placeholder="Buscar c&oacute;digo de barras" id="barcode-filter"></th>
                                <th scope="col"><input type="text" class="form-control form-control-sm" name="search_code_intern" placeholder="Buscar c&oacute;digo interno" id="code-intern-filter"></th>
                                <th scope="col"><input type="text" class="form-control form-control-sm" name="search_description" placeholder="Buscar descripci&oacute;n" id="description-filter"></th>
                                <th scope="col"><input type="text" class="form-control form-control-sm" name="search_category" placeholder="Buscar categor&iacute;a" id="category-filter"></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-success btn-save-detail" data-idalmacen="{{ $warehouse->id }}">
                        <span class="text-save-detail">Guardar cambios</span>
                        <span class="d-none text-saving-detail" role="status" aria-hidden="true">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                        <span class="text-saving-detail d-none">Guardando...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAddBarcodeStock" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <form id="form_barcode_stock" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title" id="modalAddBarcodeStock">Agregar Producto</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="barcode" class="form-label">Escanear producto:</label>
                    <input type="hidden" name="idalmacen" value="{{ $warehouse->id }}">
                    <input type="text" id="barcode" class="form-control" name="barcode" placeholder="Selecciona la caja de texto y escanea el c&oacute;digo de barras">
                    <div class="invalid-feedback">El campo no debe estar vac&iacute;o.</div>
                    <small class="text-muted d-block mt-2">Solo aplica para productos f&iacute;sicos ya registrados en este almac&eacute;n.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalStockInitial" tabindex="-1" aria-labelledby="modalStockInitialLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <form id="form_edit" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalStockInitialTitle">Stock Inicial</h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="value_stock_inicial" class="form-label" style="text-align: justify;">Ingresa el stock inicial para los productos que se a&ntilde;adir&aacute;n al establecimiento:</label>
                    <input type="text" id="value_stock_inicial" class="form-control text-uppercase" name="value_stock_inicial" required>
                    <div class="invalid-feedback">El campo no debe estar vac&iacute;o.</div>
                    <small class="text-muted d-block mt-2">Los servicios se agregar&aacute;n sin stock y no usar&aacute;n este valor.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnCloseStockInitial" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-success btn-stock-initial" data-idalmacen="{{ $warehouse->id }}">
                    <span class="text-stock-initial">Guardar cambios</span>
                    <span class="me-1 d-none text-storing-initial" role="status" aria-hidden="true">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <span class="text-storing-initial d-none">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalSumStock" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <form id="form_sum_stock" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title">Sumar Stock</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="hidden" name="idalmacen">
                    <input type="hidden" name="idproducto">
                    <label for="cantidad" class="form-label">Ingrese cantidad a sumar</label>
                    <div class="input-group">
                        <span class="input-group-text bootstrap-touchspin-down-product" style="cursor: pointer;">
                            <i class="ri-subtract-line me-1"></i>
                        </span>
                        <input type="text" class="quantity-counter text-center form-control" value="1" name="cantidad" min="1" required>
                        <span class="input-group-text bootstrap-touchspin-up-product" style="cursor: pointer;">
                            <i class="ri-add-line me-1"></i>
                        </span>
                    </div>
                    <div class="invalid-feedback">El campo no debe estar vac&iacute;o.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success btn-sum-stock">
                    <span class="text-sum-stock">Guardar cambios</span>
                    <span class="me-1 d-none text-summing-stock" role="status" aria-hidden="true">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <span class="text-summing-stock d-none">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalUpdateStock" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <form id="form_edit_stock" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title" id="modalUpdateStockTitle">Actualizar Stock</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mt-3 mb-3">
                        <input type="hidden" name="idalmacen">
                        <input type="hidden" name="idproducto">
                        <label for="stock_actual">Stock Actual</label>
                        <input type="text" id="stock_actual" class="form-control" name="stock_actual">
                        <div class="invalid-feedback">El campo no debe estar vac&iacute;o.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-success btn-store-stock">
                    <span class="text-store-stock">Guardar cambios</span>
                    <span class="me-1 d-none text-storing-stock" role="status" aria-hidden="true">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                    <span class="text-storing-stock d-none">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="excelModal" tabindex="-1" aria-labelledby="excelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="excelModalLabel">Descargar/Actualizar Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <strong>Informaci&oacute;n:</strong> Descarga el archivo Excel para editar los productos del establecimiento. Aseg&uacute;rate de que los datos est&eacute;n correctamente formateados antes de subirlo.
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <a href="{{ route('admin.export_products_warehouse', $warehouse->id) }}" class="btn btn-primary">
                        <i class="ri-file-excel-2-line align-middle"></i> Descargar Excel
                    </a>
                </div>
                <hr>
                <form id="uploadExcelForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="excelFile">Subir archivo Excel</label>
                        <input type="hidden" name="idalmacen" value="{{ $warehouse->id }}">
                        <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                    </div>

                    <button type="submit" class="btn btn-success mt-2 btn-upload-excel-products">
                        <span class="text-upload-product"><i class="ri-upload-line align-middle"></i> Actualizar productos</span>
                        <span class="me-1 d-none text-uploads-product" role="status" aria-hidden="true">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                        <span class="text-uploads-product d-none">Actualizando...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
