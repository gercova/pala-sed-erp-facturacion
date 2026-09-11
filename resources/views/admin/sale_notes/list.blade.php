@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="inbox"></i></div>
                        Gestión de Notas de venta
                    </h1>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.pos.create') }}"
                        class="dt-button create-new btn btn-success waves-effect waves-light btn-create mb-2" tabindex="0">
                        <span>
                            <i class="ri-add-circle-line align-middle"></i>
                            <span class="d-none d-sm-inline-block"> Registrar Nota</span>
                        </span>
                    </a>
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
                            <div class="small text-muted">Ventas de hoy</div>
                            <div class="h3 mb-0">{{ $kpi_today_count ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,97,242,.10); color: var(--bs-primary);">
                            <i class="fas fa-receipt"></i>
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
                            <div class="small text-muted">Total vendido hoy</div>
                            <div class="h3 mb-0">{{ $signo ?? 'S/ ' }}{{ number_format(($kpi_today_total ?? 0), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,172,105,.10); color: rgba(0,172,105,1);">
                            <i class="fas fa-cash-register"></i>
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
                            <div class="small text-muted">Pendientes</div>
                            <div class="h3 mb-0">{{ $kpi_pending ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(232,136,0,.12); color: rgba(232,136,0,1);">
                            <i class="fas fa-clock"></i>
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
                                    <th scope="col" width="15%" class="text-center">Nota</th>
                                    <th scope="col" width="11%" class="text-center">Fecha</th>
                                    <th scope="col" class="text-center" width="10%">Documento</th>
                                    <th scope="col" class="text-center">Razón social</th>
                                    <th scope="col" class="text-center" width="10%">Total</th>
                                    <th scope="col" class="text-center" width="10%">Estado</th>
                                    <th scope="col" width="12%" class="text-center">Acciones</th>
                                </tr>

                                <tr>
                                    <th>
                                        <input type="text" id="voucher-filter" class="form-control form-control-sm" placeholder="Buscar nota" />
                                    </th>
                                    <th>
                                        <input type="date" id="date-filter" class="form-control form-control-sm text-center" placeholder="Buscar fecha" max="{{ date('Y-m-d') }}" />
                                    </th>
                                    <th>
                                        <input type="text" id="document-filter" class="form-control form-control-sm" placeholder="Buscar documento" />
                                    </th>
                                    <th>
                                        <input type="text" id="reason-filter" class="form-control form-control-sm" placeholder="Buscar razón social" />
                                    </th>
                                    <th>
                                        <input type="text" id="total-filter" class="form-control form-control-sm" placeholder="Buscar total" />
                                    </th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.sale_notes.modals')
</div>

@endsection

@section('scripts')
    @include('admin.sale_notes.js-datatable')
    @include('admin.sale_notes.js-store')
@endsection
