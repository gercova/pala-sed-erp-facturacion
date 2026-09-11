@extends('admin.layout')

@section('styles')
    <style>
        .arching-card {
            border: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            overflow: visible;
        }

        .arching-card .card-body,
        .arching-card .table-responsive,
        .arching-card .dropdown,
        .arching-card td,
        .arching-card th {
            overflow: visible;
            position: relative;
        }

        .arching-card .dropdown-menu {
            z-index: 1055;
        }

        .arching-open-strip {
            border: 1px solid rgba(0, 172, 105, .14);
            background: rgba(0, 172, 105, .06);
            border-radius: 18px;
            padding: 1rem 1.15rem;
        }

        .arching-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .38rem .72rem;
            border-radius: 999px;
            background: var(--bs-tertiary-bg);
            color: var(--bs-secondary-color);
            font-size: .84rem;
            font-weight: 600;
        }

        .arching-cash-cell,
        .arching-user-cell {
            line-height: 1.2;
        }

        .arching-cash-name,
        .arching-user-name {
            font-weight: 700;
            color: var(--bs-body-color);
        }

        .arching-cash-meta,
        .arching-user-meta {
            color: var(--bs-secondary-color);
            font-size: .79rem;
        }

        .arching-money-pill {
            display: inline-flex;
            min-width: 118px;
            justify-content: center;
            align-items: center;
            padding: .5rem .8rem;
            border-radius: 999px;
            background: var(--bs-tertiary-bg);
            font-weight: 700;
        }

        .arching-kpi {
            border: 1px solid var(--bs-border-color);
            border-radius: 1rem;
            background: #fff;
            padding: 1.15rem 1.2rem;
            height: 100%;
        }

        .arching-kpi-label {
            color: var(--bs-secondary-color);
            font-size: .92rem;
            margin-bottom: .35rem;
        }

        .arching-kpi-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .arching-detail-grid,
        .arching-summary-grid {
            display: grid;
            gap: .9rem;
        }

        .arching-detail-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .arching-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .arching-detail-card,
        .arching-summary-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 1rem;
            background: #fff;
            padding: 1rem 1.05rem;
        }

        .arching-detail-card small,
        .arching-summary-card small {
            display: block;
            color: var(--bs-secondary-color);
            margin-bottom: .35rem;
            font-size: .82rem;
        }

        .arching-detail-card strong,
        .arching-summary-card strong {
            font-size: 1.02rem;
        }

        .arching-summary-card.is-final {
            background: linear-gradient(135deg, rgba(0, 172, 105, .10), rgba(0, 97, 242, .06));
            border-color: rgba(0, 172, 105, .18);
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
        #table tbody td,
        #archingMovementsTable thead th,
        #archingMovementsTable tbody td {
            vertical-align: middle;
            background: #fff !important;
        }

        #table thead th,
        #archingMovementsTable thead th {
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

        #table tbody tr:hover td,
        #archingMovementsTable tbody tr:hover td {
            background: #f8fafc !important;
        }

        #table_wrapper .form-control,
        #table_wrapper .form-select,
        #archingMovementsTable_wrapper .form-select {
            border-radius: 999px !important;
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: none;
        }

        #table_wrapper .form-control:focus,
        #table_wrapper .form-select:focus,
        #archingMovementsTable_wrapper .form-select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 .12rem rgba(59, 130, 246, .12);
        }

        #archingMovementsTable_wrapper .dataTables_filter,
        #archingMovementsTable_wrapper .dt-buttons,
        #archingMovementsTable_wrapper div.dataTables_processing {
            display: none !important;
        }

        #archingMovementsTable_wrapper .dataTables_length,
        #archingMovementsTable_wrapper .dataTables_info,
        #archingMovementsTable_wrapper .dataTables_paginate {
            padding-top: .55rem;
        }

        @media (max-width: 991.98px) {
            .arching-detail-grid,
            .arching-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .arching-detail-grid,
            .arching-summary-grid {
                grid-template-columns: 1fr;
            }
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
                        <div class="page-header-icon"><i data-feather="briefcase"></i></div>
                        Apertura y cierre de caja
                    </h1>
                </div>
                <div class="col-auto">
                    <button type="button"
                        class="btn btn-success waves-effect waves-light btn-create mb-2"
                        @disabled(! $canOpenArching)>
                        <span>
                            <i class="ri-lock-unlock-line align-middle"></i>
                            <span class="d-none d-sm-inline-block"> Aperturar caja</span>
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
            <div class="arching-kpi">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="arching-kpi-label">Caja asignada</div>
                        <div class="arching-kpi-value" style="font-size: 1.25rem;">
                            {{ $assignedCash?->descripcion ?? 'Sin caja' }}
                        </div>
                    </div>
                    <div class="rounded-3 p-3" style="background: rgba(0,97,242,.10); color: var(--bs-primary);">
                        <i class="fas fa-cash-register"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="arching-kpi">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="arching-kpi-label">Almacen activo</div>
                        <div class="arching-kpi-value" style="font-size: 1.25rem;">
                            {{ $currentWarehouse?->descripcion ?? 'No seleccionado' }}
                        </div>
                    </div>
                    <div class="rounded-3 p-3" style="background: rgba(0,172,105,.10); color: rgba(0,172,105,1);">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="arching-kpi">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="arching-kpi-label">Estado actual</div>
                        <div class="arching-kpi-value" style="font-size: 1.25rem;">
                            {{ $openArching ? 'Caja abierta' : 'Lista para abrir' }}
                        </div>
                    </div>
                    <div class="rounded-3 p-3" style="background: rgba(232,136,0,.12); color: rgba(232,136,0,1);">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($openArching)
        <div class="arching-open-strip mb-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <span class="badge bg-success-subtle text-success">Caja abierta</span>
                        <strong>{{ $openArching->cash?->descripcion ?? 'Caja activa' }}</strong>
                    </div>
                    <div class="text-muted">
                        Responsable: <strong>{{ $openArching->user?->nombres ?? auth()->user()->nombres }}</strong>
                        <span class="mx-2">|</span>
                        Apertura: <strong>{{ optional($openArching->fecha_inicio)->format('d/m/Y') ?? '-' }}</strong>
                    </div>
                </div>
                <div class="d-inline-flex gap-2 flex-wrap">
                    <span class="arching-chip">{{ $currentWarehouse?->descripcion ?? 'Almacen activo' }}</span>
                    <button type="button" class="btn btn-outline-success btn-view-summary" data-id="{{ $openArching->id }}">
                        <i class="ri-eye-line me-1"></i>Ver resumen
                    </button>
                </div>
            </div>
        </div>
    @elseif (! $assignedCash)
        <div class="alert alert-warning border-0 mb-4">
            Este usuario no tiene una caja asignada. Asignale una caja para poder aperturar y cerrar movimientos.
        </div>
    @endif

    <div class="card shadow-sm mb-4 arching-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table" class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th width="12%" class="text-center">Fecha</th>
                            <th width="18%" class="text-center">Responsable</th>
                            <th>Caja</th>
                            <th width="14%" class="text-center">Apertura</th>
                            <th width="14%" class="text-center">Cierre</th>
                            <th width="10%" class="text-center">Estado</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                        <tr class="filters">
                            <th>
                                <input type="date" class="form-control form-control-sm text-center" id="date-filter" max="{{ date('Y-m-d') }}">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm" id="responsible-filter" placeholder="Buscar responsable">
                            </th>
                            <th>
                                <select class="form-select form-select-sm" id="cash-filter">
                                    <option value="">Todas</option>
                                    @foreach ($filterCashes as $cash)
                                        <option value="{{ $cash->id }}">{{ $cash->descripcion }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th></th>
                            <th></th>
                            <th>
                                <select class="form-select form-select-sm" id="status-filter">
                                    <option value="">Todos</option>
                                    <option value="1">Abierta</option>
                                    <option value="2">Cerrada</option>
                                </select>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('admin.arching_cashes.modals')
</div>
@endsection

@section('scripts')
    @include('admin.arching_cashes.js-datatable')
    @include('admin.arching_cashes.js-store')
@endsection
