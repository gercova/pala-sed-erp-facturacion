@extends('admin.layout')

@section('styles')
    <style>
        .billing-list-shell .card-body,
        .billing-list-shell .table-responsive {
            overflow: visible;
        }

        .billing-list-shell .dropdown-menu {
            z-index: 1055;
        }

        .billing-doc-cell,
        .billing-customer-cell {
            line-height: 1.2;
        }

        .billing-doc-code,
        .billing-customer-name {
            font-weight: 600;
            color: var(--bs-body-color);
        }

        .billing-doc-type,
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

        #table tbody tr {
            background: #fff !important;
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
                        <div class="page-header-icon"><i data-feather="file-text"></i></div>
                        Gestión de Comprobantes
                    </h1>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.pos.create') }}"
                        class="dt-button create-new btn btn-success waves-effect waves-light btn-create mb-2" tabindex="0">
                        <span>
                            <i class="ri-add-circle-line align-middle"></i>
                            <span class="d-none d-sm-inline-block"> Vender</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row">
        <div class="col-12">
    <div class="card custom-card pro-card billing-list-shell">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table" class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th width="12%" class="text-center">Fecha</th>
                            <th width="18%" class="text-center">Comprobante</th>
                            <th>Cliente</th>
                            <th width="12%" class="text-center">Almacén</th>
                            <th width="12%" class="text-center">Total</th>
                            <th width="6%" class="text-center">XML</th>
                            <th width="6%" class="text-center">CDR</th>
                            <th width="10%" class="text-center">SUNAT</th>
                            <th width="10%" class="text-center">Estado</th>
                            <th width="12%" class="text-center">Acciones</th>
                        </tr>
                        <tr>
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
    </div>

    @include('admin.billings.modals')
</div>
@endsection

@section('scripts')
    @include('admin.billings.js-datatable')
    @include('admin.billings.js-store')
@endsection
