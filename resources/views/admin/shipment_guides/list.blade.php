@extends('admin.layout')

@section('styles')
    <style>
        .guide-list-card {
            border: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            overflow: visible;
        }

        .guide-list-card .card-body,
        .guide-list-card .table-responsive {
            overflow: visible;
        }

        .guide-list-card .dropdown-menu {
            z-index: 1055;
        }

        #table {
            --bs-table-bg: #fff;
            --bs-table-striped-bg: #fff;
            --bs-table-active-bg: #f8fafc;
            --bs-table-hover-bg: #f8fafc;
            background: #fff;
        }

        #table thead th,
        #table thead td,
        #table tbody td {
            vertical-align: middle;
            background: #fff !important;
        }

        #table thead th {
            color: #4b5563;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
        }

        #table tbody tr:hover td {
            background: #f8fafc !important;
        }

        #table_wrapper .form-control,
        #table thead .form-control {
            border-radius: 999px !important;
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: none;
        }

        #table_wrapper .form-control:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 .12rem rgba(59, 130, 246, .12);
        }
    </style>
@endsection

@section('content')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="truck"></i></div>
                            Gestión de Guías de remisión
                        </h1>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.create_shipment_guide') }}" class="dt-button create-new btn btn-success waves-effect waves-light mb-2">
                            <span>
                                <i class="ri-add-circle-line align-middle"></i>
                                <span class="d-none d-sm-inline-block"> Nueva guía</span>
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
                                <div class="small text-muted">Guías de hoy</div>
                                <div class="h3 mb-0">{{ $kpi_today_count }}</div>
                            </div>
                            <div class="rounded-3 p-3" style="background: rgba(0,97,242,.10); color: var(--bs-primary);">
                                <i class="fas fa-road"></i>
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
                                <div class="small text-muted">Pendientes GRE</div>
                                <div class="h3 mb-0">{{ $kpi_today_pending }}</div>
                            </div>
                            <div class="rounded-3 p-3" style="background: rgba(232,136,0,.12); color: rgba(232,136,0,1);">
                                <i class="fas fa-paper-plane"></i>
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
                                <div class="small text-muted">Clientes del día</div>
                                <div class="h3 mb-0">{{ $kpi_today_customers }}</div>
                            </div>
                            <div class="rounded-3 p-3" style="background: rgba(0,172,105,.10); color: rgba(0,172,105,1);">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4 guide-list-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table" class="table table-hover table-sm mb-0">
                        <thead>
                            <tr>
                                <th width="12%" class="text-center">Fecha</th>
                                <th width="18%" class="text-center">Guía</th>
                                <th>Destinatario</th>
                                <th width="10%" class="text-center">Modo</th>
                                <th width="12%" class="text-center">Almacén</th>
                                <th width="6%" class="text-center">XML</th>
                                <th width="6%" class="text-center">CDR</th>
                                <th width="10%" class="text-center">GRE</th>
                                <th width="10%" class="text-center">Acciones</th>
                            </tr>
                            <tr>
                                <th><input type="date" class="form-control form-control-sm text-center" id="filter_date"></th>
                                <th><input type="text" class="form-control form-control-sm text-center" id="filter_document" placeholder="Serie o correlativo"></th>
                                <th><input type="text" class="form-control form-control-sm" id="filter_customer" placeholder="Buscar cliente"></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.shipment_guides.modals')
@endsection

@section('scripts')
    @include('admin.shipment_guides.js-datatable')
    @include('admin.shipment_guides.js-store')
@endsection
