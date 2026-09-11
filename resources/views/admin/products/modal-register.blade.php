@php
    $defaultUnitId = $units->first()?->id;
    $defaultCategoryId = $categories->first()?->id;
    $defaultIgvTypeId = $igvTypeAffections->firstWhere('codigo', '10')?->id ?? $igvTypeAffections->first()?->id;
@endphp

<div class="modal fade" id="modalAddProduct" tabindex="-1" aria-labelledby="modalAddProductLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="form_save_product" class="modal-content border-0 shadow-lg" onsubmit="event.preventDefault()">
            @csrf

            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="modalAddProductTitle">
                    <i class="fas fa-edit text-primary me-2"></i> Gesti&oacute;n de Inventario y Servicios
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert bg-light border-0 shadow-sm mb-4" style="border-left: 4px solid #0d6efd;">
                    <div class="d-flex">
                        <div class="me-2 text-primary">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="small text-muted">
                            Este cat&aacute;logo ya queda preparado para facturaci&oacute;n electr&oacute;nica con
                            <strong>c&oacute;digo SUNAT</strong>, <strong>afectaci&oacute;n IGV</strong> y distinci&oacute;n entre
                            <strong>producto</strong> y <strong>servicio</strong>.
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="descripcion" class="form-label small fw-bold text-muted text-uppercase">Nombre del Producto o Servicio</label>
                        <input type="text" id="descripcion" class="form-control form-control-lg text-uppercase border-2" name="descripcion" placeholder="Ej: MANTENIMIENTO PREVENTIVO / DISCO DURO SSD 500GB" required>
                    </div>

                    <div class="col-md-4">
                        <label for="codigo_interno" class="form-label small fw-bold text-muted text-uppercase">C&oacute;d. Interno</label>
                        <input type="text" id="codigo_interno" class="form-control bg-light" name="codigo_interno" placeholder="Opcional">
                    </div>

                    <div class="col-md-4">
                        <label for="codigo_barras" class="form-label small fw-bold text-muted text-uppercase">C&oacute;d. Barras</label>
                        <input type="text" id="codigo_barras" class="form-control bg-light" name="codigo_barras" placeholder="Opcional">
                    </div>

                    <div class="col-md-4">
                        <label for="codigo_sunat" class="form-label small fw-bold text-muted text-uppercase">C&oacute;d. SUNAT</label>
                        <input type="text" id="codigo_sunat" class="form-control bg-light" name="codigo_sunat" placeholder="Opcional">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="idunidad" class="form-label small fw-bold text-muted text-uppercase">Unidad</label>
                        <select name="idunidad" id="idunidad" class="form-select product-select" data-default="{{ $defaultUnitId }}">
                            <option value="">Seleccione...</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->descripcion }} ({{ $unit->codigo }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="idcategoria" class="form-label small fw-bold text-muted text-uppercase">Categor&iacute;a</label>
                        <select name="idcategoria" id="idcategoria" class="form-select product-select" data-default="{{ $defaultCategoryId }}">
                            <option value="">Seleccione categor&iacute;a...</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="idcodigo_igv" class="form-label small fw-bold text-muted text-uppercase">Afectaci&oacute;n IGV</label>
                        <select name="idcodigo_igv" id="idcodigo_igv" class="form-select product-select" data-default="{{ $defaultIgvTypeId }}">
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
                            <input type="radio" class="btn-check" name="opcion" id="producto" value="1" checked>
                            <label class="btn btn-outline-primary py-2" for="producto">
                                <i class="fas fa-boxes me-1"></i> Producto
                            </label>

                            <input type="radio" class="btn-check" name="opcion" id="servicio" value="2">
                            <label class="btn btn-outline-primary py-2" for="servicio">
                                <i class="fas fa-tools me-1"></i> Servicio
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="text-muted opacity-25">

                <div class="row g-4" id="section-finance">
                    <div class="col-md-4 finance-col">
                        <label for="precio_compra" class="form-label small fw-bold text-muted text-uppercase text-primary">Costo / Compra</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white border-primary fw-bold">{{ $signo }}</span>
                            <input type="number" id="precio_compra" class="form-control border-primary shadow-sm" name="precio_compra" value="0.00" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="col-md-4 finance-col">
                        <label for="precio_venta" class="form-label small fw-bold text-muted text-uppercase text-success">Precio Venta</label>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white border-success fw-bold">{{ $signo }}</span>
                            <input type="number" id="precio_venta" class="form-control border-success shadow-sm" name="precio_venta" value="0.00" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="col-md-4" id="container-stock">
                        <label for="stock_actual" class="form-label small fw-bold text-muted text-uppercase text-danger">Stock Inicial</label>
                        <div class="input-group">
                            <span class="input-group-text bg-danger text-white border-danger"><i class="fas fa-layer-group"></i></span>
                            <input type="number" id="stock_actual" class="form-control border-danger shadow-sm" name="stock_actual" value="0" min="0" step="1">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4 fw-bold text-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary px-5 fw-bold shadow-sm btn-save-product" type="submit">
                    <span class="text-save-product"><i class="fas fa-check-circle me-1"></i> Guardar Producto</span>
                    <span class="d-none text-saving-product"><i class="fas fa-spinner fa-spin me-1"></i> Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
