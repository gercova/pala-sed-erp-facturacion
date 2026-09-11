@extends('admin.layout')

@section('styles')
    <style>
        .billing-list-shell .card-body,
        .billing-list-shell .table-responsive {
            overflow: visible;
        }

        .billing-customer-cell {
            line-height: 1.2;
        }

        .billing-customer-name {
            font-weight: 600;
            color: var(--bs-body-color);
        }

        .billing-customer-doc {
            color: var(--bs-secondary-color);
            font-size: .79rem;
        }

        .billing-total-chip {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-width: 116px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            font-weight: 700;
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

        #table_wrapper .dataTables_length,
        #table_wrapper .dataTables_info,
        #table_wrapper .dataTables_paginate {
            padding-top: .5rem;
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
                        <div class="page-header-icon"><i data-feather="shopping-cart"></i></div>
                        Punto de venta
                    </h1>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.pos.create') }}"
                        class="dt-button create-new btn btn-success waves-effect waves-light btn-create mb-2" tabindex="0">
                        <span>
                            <i class="ri-add-circle-line align-middle"></i>
                            <span class="d-none d-sm-inline-block"> Comenzar a vender</span>
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
                            <div class="small text-muted">Boletas de hoy</div>
                            <div class="h5 mb-1">Cantidad: {{ $kpi_boletas_count ?? 0 }}</div>
                            <div class="small text-muted">Monto: {{ $signo ?? 'S/' }} {{ number_format((float) ($kpi_boletas_total ?? 0), 2) }}</div>
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
                            <div class="small text-muted">Facturas de hoy</div>
                            <div class="h5 mb-1">Cantidad: {{ $kpi_facturas_count ?? 0 }}</div>
                            <div class="small text-muted">Monto: {{ $signo ?? 'S/' }} {{ number_format((float) ($kpi_facturas_total ?? 0), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,172,105,.10); color: rgba(0,172,105,1);">
                            <i class="fas fa-file-invoice-dollar"></i>
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
                            <div class="small text-muted">Notas de venta de hoy</div>
                            <div class="h5 mb-1">Cantidad: {{ $kpi_sale_notes_count ?? 0 }}</div>
                            <div class="small text-muted">Monto: {{ $signo ?? 'S/' }} {{ number_format((float) ($kpi_sale_notes_total ?? 0), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(232,136,0,.12); color: rgba(232,136,0,1);">
                            <i class="fas fa-cash-register"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card custom-card pro-card billing-list-shell">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th width="12%" class="text-center">Fecha</th>
                                    <th width="14%" class="text-center">Tipo</th>
                                    <th width="16%" class="text-center">Documento</th>
                                    <th>Cliente</th>
                                    <th width="12%" class="text-center">Total</th>
                                    <th width="12%" class="text-center">Estado</th>
                                </tr>
                                <tr>
                                    <th>
                                        <input type="date" id="date-filter" class="form-control form-control-sm text-center" max="{{ date('Y-m-d') }}">
                                    </th>
                                    <th>
                                        <input type="text" id="type-filter" class="form-control form-control-sm text-center" placeholder="Tipo">
                                    </th>
                                    <th>
                                        <input type="text" id="document-filter" class="form-control form-control-sm text-center" placeholder="Serie o correlativo">
                                    </th>
                                    <th>
                                        <input type="text" id="customer-filter" class="form-control form-control-sm" placeholder="Buscar cliente">
                                    </th>
                                    <th>
                                        <input type="text" id="total-filter" class="form-control form-control-sm" placeholder="Buscar total">
                                    </th>
                                    <th>
                                        <input type="text" id="status-filter" class="form-control form-control-sm text-center" placeholder="Estado">
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('admin.pos.js-datatable')
@endsection
