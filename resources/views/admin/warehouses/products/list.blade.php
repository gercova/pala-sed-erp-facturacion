@extends('admin.layout')
@section('styles')
<style>
    #table_detail tfoot {
        display: table-header-group;
    }

    .table-fade {
        transition: opacity 0.3s ease-in-out;
        opacity: 1;
    }

    .table-fade.out {
        opacity: 0;
    }

    .warehouse-module-note {
        font-size: 0.9rem;
        color: #6b7280;
    }

    #loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(255, 255, 255, 0.8);
        padding: 10px 20px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        display: none;
    }
</style>
@endsection

@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3 mb-3">
                <div class="col-auto mt-3 mb-3">
                    <h1 class="page-header-title">
                        {{ $warehouse->descripcion }} - Productos
                    </h1>
                    <div class="warehouse-module-note mt-1">
                        Administra precios y stock de este almac&eacute;n. Los servicios se muestran, pero no manejan stock f&iacute;sico.
                    </div>
                </div>
                <div class="col-auto">
                    <div class="dt-action-buttons d-flex gap-2">
                        <button class="btn btn-success btn-sm btn-download-excel" data-toggle="modal" data-target="#excelModal">
                            <i class="ri-file-excel-2-line align-middle"></i>
                            <span class="d-none d-sm-inline">Descargar/Actualizar Excel</span>
                        </button>

                        <button class="btn btn-info btn-sm btn-add-for-barcode">
                            <i class="ri-barcode-line align-middle"></i>
                            <span class="d-none d-sm-inline">Agregar por C&oacute;digo de Barras</span>
                        </button>

                        <button class="btn btn-primary btn-sm btn-add-product" value="{{ $warehouse->id }}">
                            <i class="ri-add-circle-line align-middle"></i>
                            <span class="d-none d-sm-inline">Agregar Productos</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card custom-card pro-card">
                <div class="card-body" style="padding-top: 8px;">
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm d-flex align-items-center py-3 px-3" role="alert">
                        <i class="ri-alert-line fs-5 text-warning me-2"></i>
                        <div class="flex-grow-1">
                            <strong>Atenci&oacute;n:</strong> Solo personal autorizado debe modificar esta informaci&oacute;n.
                            Para guardar cambios r&aacute;pidos en precios o stock m&iacute;nimo, escribe el valor y presiona <kbd>Enter</kbd>.
                            Los servicios no descuentan ni suman stock.
                        </div>
                    </div>

                    <div class="table-responsive mt-2">
                        <div id="loading" style="display:none;">
                            <div class="spinner"></div>
                            Cargando...
                        </div>
                        <table id="table" class="table table-bordered table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="text-center" width="12%">C&oacute;digo Barras</th>
                                    <th class="text-center" width="12%">C&oacute;digo Interno</th>
                                    <th class="text-left">Descripci&oacute;n</th>
                                    <th class="text-center" width="12%">P. Compra</th>
                                    <th class="text-center" width="12%">P. Venta</th>
                                    <th class="text-center" width="10%">Stock M&iacute;nimo</th>
                                    <th class="text-center" width="10%">Stock Actual</th>
                                    <th class="text-center" width="10%">Acciones</th>
                                </tr>
                                <tr>
                                    <th><input type="text" class="form-control form-control-sm text-center" placeholder="Buscar c&oacute;digo de barras" id="barcode-filter-list"></th>
                                    <th><input type="text" class="form-control form-control-sm text-center" placeholder="Buscar c&oacute;digo interno" id="code-intern-filter-list"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar descripci&oacute;n" id="description-filter-list"></th>
                                    <th><input type="text" class="form-control form-control-sm text-center" placeholder="Buscar precio compra" id="price-buy-filter-list"></th>
                                    <th><input type="text" class="form-control form-control-sm text-center" placeholder="Buscar precio venta" id="price-sale-filter-list"></th>
                                    <th><input type="text" class="form-control form-control-sm text-center" placeholder="Buscar stock m&iacute;nimo" id="stock-min-filter-list"></th>
                                    <th><input type="text" class="form-control form-control-sm text-center" placeholder="Buscar stock actual" id="stock-act-filter-list"></th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.warehouses.products.modals')
</div>

@endsection

@section('scripts')
    @include('admin.warehouses.products.js-datatable')
    @include('admin.warehouses.products.js-store')
@endsection
