<div class="modal fade" id="modalAddToProduct" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <form id="form_save_to_product" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddToProductTitle">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3 {{ request()->is('create-sale-note') ? 'd-none' : '' }}">
                        <label for="idalmacen">Establecimiento</label>
                        <select name="idalmacen" id="idalmacen" class="form-control">
                            <option value=""></option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}"> {{ $warehouse->descripcion }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div id="wrapper-product" class="col-12 mb-3 d-none">
                        <label for="product">Producto</label>
                        <select name="product" id="product" class="form-control"></select>
                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="correlativo">Cantidad</label>
                        <div class="input-group">
                            <span class="input-group-text bootstrap-touchspin-down-product" style="cursor: pointer;"><i
                                    class="ri-subtract-line me-sm-1"></i></span>
                            <input type="text" class="quantity-counter text-center form-control" value="1"
                                name="cantidad">
                            <span class="input-group-text bootstrap-touchspin-up-product" style="cursor: pointer;"><i
                                    class="ri-add-line me-sm-1"></i></span>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="precio">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text" id="precio">S/</span>
                            <input type="text" id="precio" class="form-control" name="precio">
                            <div class="invalid-feedback">El campo no debe estar vacío.</div>
                        </div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-save">
                            <span class="text-save">Guardar</span>
                            <span class="spinner-border spinner-border-sm me-1 d-none text-saving" role="status"
                                aria-hidden="true"></span>
                            <span class="text-saving d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>