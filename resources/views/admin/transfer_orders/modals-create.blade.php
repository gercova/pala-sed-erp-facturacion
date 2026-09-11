<style>
    /* Modo claro */
    [data-theme-mode="light"] #wrapper__search .hover-row {
        background-color: #f0f0f0 !important; /* Color claro */
    }

    [data-theme-mode="light"] #wrapper__search .hover-row td {
        background-color: #f0f0f0 !important; /* Cambia el fondo de las celdas */
    }

    /* Modo oscuro */
    [data-theme-mode="dark"] #wrapper__search .hover-row {
        background-color: #444444 !important; /* Color oscuro */
        color: white; /* Cambia el color del texto para mejor visibilidad */
    }

    /* Modo claro */
    [data-theme-mode="light"] #wrapper_info_detail .hover-row {
        background-color: #f0f0f0 !important; /* Color claro */
    }

    [data-theme-mode="dark"] #wrapper__search .hover-row td {
        background-color: #444444 !important; /* Cambia el fondo de las celdas */
        color: white; /* Cambia el color del texto para mejor visibilidad */
    }

    /* Modo claro */
    [data-theme-mode="light"] #wrapper_info_detail .hover-row {
        background-color: #f0f0f0 !important; /* Color claro */
    }

    [data-theme-mode="light"] #wrapper_info_detail .hover-row td {
        background-color: #f0f0f0 !important; /* Cambia el fondo de las celdas */
    }

    /* Modo oscuro */
    [data-theme-mode="dark"] #wrapper_info_detail .hover-row {
        background-color: #444444 !important; /* Color oscuro */
        color: white; /* Cambia el color del texto para mejor visibilidad */
    }

    [data-theme-mode="dark"] #wrapper_info_detail .hover-row td {
        background-color: #444444 !important; /* Cambia el fondo de las celdas */
        color: white; /* Cambia el color del texto para mejor visibilidad */
    }

    #wrapper_info_detail .card {
        border: none; /* Elimina el borde predeterminado de la tarjeta */
    }

    #wrapper_info_detail .form-control {
        border-radius: 0.25rem; /* Bordes redondeados */
        transition: border-color 0.3s, box-shadow 0.3s; /* Animación en el borde */
    }

    #wrapper_info_detail .form-control:focus {
        border-color: #80bdff; /* Color del borde al enfocar */
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); /* Sombra al enfocar */
    }

    #wrapper_info_detail .table th {
        background-color: #f8f9fa; /* Color de fondo para encabezados */
        font-weight: bold;
    }

    [data-theme-mode="dark"] #wrapper_info_detail .table {
        background-color: #343a40; /* Fondo oscuro */
        color: #ffffff; /* Texto blanco */
    }

    [data-theme-mode="dark"] #wrapper_info_detail .table th {
        background-color: #495057; /* Fondo para los encabezados en modo oscuro */
        color: #ffffff; /* Color del texto en encabezados en modo oscuro */
    }

    #wrapper_info_detail .table tr:hover {
        background-color: #e2e6ea; /* Color al pasar el ratón sobre la fila */
    }

    [data-theme-mode="dark"] #wrapper_info_detail .table tr:hover {
        background-color: #6c757d; /* Color al pasar el ratón sobre la fila en modo oscuro */
    }

    #wrapper_info_detail .table td {
        vertical-align: middle; /* Alineación vertical del contenido de las celdas */
    }

</style>
<div class="modal fade" id="modalAddToProduct" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form id="form_add_to_product" class="modal-content" onsubmit="event.preventDefault()">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddToProductTitle">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-xs-6 col-sm-12 col-md-4 col-lg-12 col-xl-12">
                        <label class="form-label">Producto:</label>
                        <div class="input-group">
                            <input type="text" class="form-control input__search"
                                placeholder="Buscar por nombre o código de producto"
                                name="input__search" autocomplete="off">
                            <span class="input-group-text text-danger btn-clear-input" id="basic-addon11" style="cursor: pointer;"
                                data-bs-toggle="tooltip" data-bs-original-title="Limpiar descripción">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="feather feather-x align-middle mr-25">
                                <line x1="18" y1="6" x2="6"
                                    y2="18"></line>
                                <line x1="6" y1="6" x2="18"
                                    y2="18"></line>
                            </svg>
                            </span>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <div class="row">
                            <div class="col-12 col-md-7 mt-2">
                                <label class="fw-bold">Resultado:</label>
                                <div id="wrapper__table__detail" class="table-responsive-sm border rounded shadow-sm p-2" style="width: 100%; height: 350px; background: #f8f9fa;">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <input type="hidden" name="idproducto">
                                                <th>
                                                    <div class="cell text-left">CÓDIGO</div>
                                                </th>
                                                <th>
                                                    <div class="cell text-left">DESCRIPCIÓN</div>
                                                </th>
                                                <th>
                                                    <div class="cell text-end">PRECIO</div>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                    <table class="table table-hover table-sm">
                                        <tbody id="wrapper__search"></tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div id="wrapper_info_detail" class="col-12 col-md-5 mt-2">
                                <div class="card shadow-sm border rounded">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="form-group">
                                                    <label class="fw-bold">Cantidad:</label>
                                                    <div class="border rounded d-flex align-items-center">
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light border-0 text-primary bootstrap-touchspin-down-product" style="cursor: pointer;">
                                                                <i class="ri-subtract-line"></i>
                                                            </span>
                                                            <input type="text" class="quantity-counter text-center form-control" value="1" name="input-cantidad">
                                                            <span class="input-group-text bg-light border-0 text-primary bootstrap-touchspin-up-product" style="cursor: pointer;">
                                                                <i class="ri-add-line"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="form-group">
                                                    <label class="fw-bold">Precio Unitario</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light border">S/</span>
                                                        <input type="text" class="form-control" name="input-price">
                                                        <div class="invalid-feedback">El campo no debe estar vacío.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-12 mt-4">
                                                <h6 class="my-2 border-bottom pb-2 text-primary">INFORMACIÓN</h6>
                                            </div>
                            
                                            <div class="col-md-12 mt-2">
                                                <div class="form-group">
                                                    <label id="label__stock" class="fw-bold">STOCK:</label>
                                                    <div class="table-responsive-sm border rounded p-2 bg-light">
                                                        <table id="table_info" class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-left">Descripción</th>
                                                                    <th class="text-end">Stock Actual</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="wrapper__warehouses"></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end mt-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-save-to-product">
                            <span class="text-save-to-product">Guardar cambios</span>
                            <span class="me-1 d-none text-saving-to-product" role="status"
                                aria-hidden="true">
                                <i class="fas fa-spinner fa-spin"></i></span>
                            <span class="text-saving-to-product d-none">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>