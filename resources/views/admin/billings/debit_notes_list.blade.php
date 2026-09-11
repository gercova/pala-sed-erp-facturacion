@extends('admin.layout')

@section('styles')
    <style>
        .billing-list-card {
            border: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            overflow: visible;
        }

        .billing-list-card .card-body,
        .billing-list-card .table-responsive {
            overflow: visible;
        }

        .billing-list-card .dropdown,
        .billing-list-card td,
        .billing-list-card th {
            position: relative;
        }

        .billing-list-card .dropdown-menu {
            z-index: 1055;
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

        .billing-file-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--bs-tertiary-bg);
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

        #table thead tr.filters th {
            background: #fff !important;
            border-bottom: 1px solid #eef2f7;
            padding-top: .55rem;
            padding-bottom: .55rem;
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
        #table_wrapper .form-select,
        #table thead .form-control {
            border-radius: 999px !important;
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: none;
        }

        #table_wrapper .form-control:focus,
        #table_wrapper .form-select:focus {
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
                        <div class="page-header-icon"><i data-feather="corner-up-right"></i></div>
                        Gestión de Notas de débito
                    </h1>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.billings') }}"
                        class="dt-button create-new btn btn-outline-primary waves-effect waves-light mb-2" tabindex="0">
                        <span>
                            <i class="ri-arrow-left-line align-middle"></i>
                            <span class="d-none d-sm-inline-block"> Volver a comprobantes</span>
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
                            <div class="small text-muted">Emitidas hoy</div>
                            <div class="h3 mb-0">{{ $kpi_today_count ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,97,242,.10); color: var(--bs-primary);">
                            <i class="fas fa-file-signature"></i>
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
                            <div class="small text-muted">Total emitido hoy</div>
                            <div class="h3 mb-0">{{ $signo ?? 'S/' }} {{ number_format(($kpi_today_total ?? 0), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,172,105,.10); color: rgba(0,172,105,1);">
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
                            <div class="small text-muted">Pendientes SUNAT</div>
                            <div class="h3 mb-0">{{ $kpi_sunat_pending ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(232,136,0,.12); color: rgba(232,136,0,1);">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4 billing-list-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table" class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th width="12%" class="text-center">Fecha</th>
                            <th width="18%" class="text-center">Nota</th>
                            <th>Cliente</th>
                            <th width="12%" class="text-center">Almacén</th>
                            <th width="12%" class="text-center">Total</th>
                            <th width="6%" class="text-center">XML</th>
                            <th width="6%" class="text-center">CDR</th>
                            <th width="10%" class="text-center">SUNAT</th>
                            <th width="10%" class="text-center">Estado</th>
                            <th width="12%" class="text-center">Acciones</th>
                        </tr>
                        <tr class="filters">
                            <th>
                                <input type="date" id="date-filter" class="form-control form-control-sm text-center" max="{{ date('Y-m-d') }}">
                            </th>
                            <th>
                                <input type="text" id="voucher-filter" class="form-control form-control-sm text-center" placeholder="Serie o correlativo">
                            </th>
                            <th>
                                <input type="text" id="reason-filter" class="form-control form-control-sm" placeholder="Buscar cliente">
                            </th>
                            <th></th>
                            <th>
                                <input type="text" id="total-filter" class="form-control form-control-sm" placeholder="Buscar total">
                            </th>
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
@endsection

@section('scripts')
    @include('admin.billings.js-debit-notes-datatable')
    @include('admin.billings.js-store')
@endsection
