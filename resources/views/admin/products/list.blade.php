@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="package"></i></div>
                        Gesti&oacute;n de Productos
                    </h1>
                </div>
                <div class="col-auto">
                    <button class="dt-button create-new btn btn-success waves-effect waves-light btn-create-product mb-3" tabindex="0">
                        <i class="ri-add-circle-line align-middle"></i>
                        <span class="d-none d-sm-inline">Agregar producto</span>
                    </button>

                    <button class="dt-button create-new btn btn-info waves-effect waves-light btn-upload ml-2 mb-3" tabindex="0">
                            <span>
                                <i class="ri-upload-2-line align-middle"></i> 
                                <span class="d-none d-sm-inline-block">Cargar Excel</span>
                            </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card custom-card pro-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Productos</div>
                            <div class="h3 mb-0">{{ $kpi_total_products ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,97,242,.10); color: var(--bs-primary);">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card pro-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Stock bajo (≤ 5)</div>
                            <div class="h3 mb-0">{{ $kpi_low_stock ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(232,21,0,.10); color: rgba(232,21,0,1);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card pro-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Actualizados hoy</div>
                            <div class="h3 mb-0">{{ $kpi_updated_today ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,172,105,.10); color: rgba(0,172,105,1);">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card custom-card pro-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">Descripci&oacute;n</th>
                                    <th scope="col" width="10%" class="text-center">Und.</th>
                                    <th scope="col" width="15%" class="text-center">Precio Compra</th>
                                    <th scope="col" width="15%" class="text-center">Precio Venta</th>
                                    <th scope="col" width="12%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.products.modal-register')
    @include('admin.products.modals')
    @include('admin.categories.modal-register')
</div>

@endsection

@section('scripts')
    @include('admin.products.js-datatable')
    @include('admin.products.js-register')
    @include('admin.categories.js-register')
    @include('admin.products.js-store')
@endsection
