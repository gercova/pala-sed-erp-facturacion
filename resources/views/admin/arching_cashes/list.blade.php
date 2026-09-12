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

        .reconcile-metric-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.75rem;
            background: #fff;
            padding: 1.15rem 1.25rem;
            height: 100%;
        }

        .reconcile-metric-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .jug-stat-box {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.5rem;
            padding: 0.75rem 0.5rem;
            text-align: center;
            height: 100%;
        }

        .jug-stat-box .jug-count {
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 0.2rem;
        }

        .jug-stat-box .jug-label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .jug-aptos {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .jug-danados {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .jug-prestados {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        .jug-vendidos {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
        }

        .open-cash-card {
            border: 1px solid #bbf7d0;
            background: #f8fafc;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }

        .open-cash-card:hover {
            border-color: #86efac;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .open-cash-highlight {
            border-left: 4px solid #10b981 !important;
        }

        .arching-detail-grid,
        .arching-summary-grid {
            display: grid;
            gap: .85rem;
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
            border-radius: 0.75rem;
            background: #fff;
            padding: 0.95rem 1rem;
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
            font-size: 1.05rem;
        }

        .arching-summary-card.is-final {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .arching-summary-card.is-cash {
            background: #eff6ff;
            border-color: #bfdbfe;
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
                        Arqueo, Cuadre y Operaciones de Caja
                    </h1>
                    <div class="text-muted small mt-1">
                        Control de turnos, reconciliación financiera, balance de bidones y verificación de cajas abiertas
                    </div>
                </div>
                <div class="col-auto mb-3">
                    <button type="button"
                        class="btn btn-primary waves-effect waves-light btn-create"
                        @disabled(! $canOpenArching)>
                        <i class="ri-lock-unlock-line me-1"></i>
                        <span>Aperturar nueva caja</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4">
    {{-- MODULO DE CUADRE Y OPERACIONES DEL DIA --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="ri-dashboard-line me-2 text-primary"></i>Cuadre y Conciliación del Día
                </h5>
                <small class="text-muted">Operaciones consolidadas del {{ date('d/m/Y') }} (Almacén: {{ $currentWarehouse?->descripcion ?? 'Todos' }})</small>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="ri-calendar-event-line me-1 text-muted"></i>Hoy: {{ date('d/m/Y') }}
            </span>
        </div>
        <div class="card-body p-4 bg-light">
            <div class="row g-3">
                {{-- KPI 1: Ventas Totales --}}
                <div class="col-xl-3 col-md-6">
                    <div class="reconcile-metric-card shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Ventas del Día</span>
                            <div class="reconcile-metric-icon bg-success-subtle text-success">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                        </div>
                        <div class="h3 fw-bold text-dark mb-1">
                            {{ $signo }} {{ number_format($dailyReconciliation['sales_total'], 2) }}
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="ri-file-check-line me-1 text-success"></i>{{ $dailyReconciliation['sales_count'] }} ventas vigentes</span>
                            @if ($dailyReconciliation['annulled_count'] > 0)
                                <span class="text-danger fw-semibold" title="Comprobantes anulados">
                                    <i class="ri-close-circle-line me-1"></i>{{ $dailyReconciliation['annulled_count'] }} anulación(es)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- KPI 2: Pedidos y Repartos --}}
                <div class="col-xl-3 col-md-6">
                    <div class="reconcile-metric-card shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Pedidos y Repartos</span>
                            <div class="reconcile-metric-icon bg-primary-subtle text-primary">
                                <i class="ri-e-bike-2-line"></i>
                            </div>
                        </div>
                        <div class="h3 fw-bold text-dark mb-1">
                            {{ $dailyReconciliation['total_deliveries'] }} / {{ $dailyReconciliation['total_orders'] }}
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="ri-checkbox-circle-line me-1 text-primary"></i>Entregas realizadas</span>
                            <span class="fw-semibold text-dark">{{ $signo }} {{ number_format($dailyReconciliation['deliveries_total'], 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- KPI 3: Anulaciones --}}
                <div class="col-xl-2 col-md-6">
                    <div class="reconcile-metric-card shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Anulaciones</span>
                            <div class="reconcile-metric-icon bg-danger-subtle text-danger">
                                <i class="ri-file-damage-line"></i>
                            </div>
                        </div>
                        <div class="h3 fw-bold text-danger mb-1">
                            {{ $signo }} {{ number_format($dailyReconciliation['annulled_total'], 2) }}
                        </div>
                        <div class="text-muted small">
                            <span><i class="ri-alert-line me-1 text-danger"></i>{{ $dailyReconciliation['annulled_count'] }} comprobantes</span>
                        </div>
                    </div>
                </div>

                {{-- KPI 4: Cuadre de Bidones / Envases (4 buckets) --}}
                <div class="col-xl-4 col-md-6">
                    <div class="reconcile-metric-card shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Cuadre de Envases (20L)</span>
                            <span class="badge bg-secondary-subtle text-secondary">Día actual</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-3">
                                <div class="jug-stat-box jug-aptos">
                                    <div class="jug-count">{{ $dailyReconciliation['jugs_intact'] }}</div>
                                    <div class="jug-label">Aptos</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="jug-stat-box jug-danados">
                                    <div class="jug-count">{{ $dailyReconciliation['jugs_damaged'] }}</div>
                                    <div class="jug-label">Dañados</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="jug-stat-box jug-prestados">
                                    <div class="jug-count">{{ $dailyReconciliation['jugs_loaned'] }}</div>
                                    <div class="jug-label">Prestados</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="jug-stat-box jug-vendidos">
                                    <div class="jug-count">{{ $dailyReconciliation['jugs_sold'] }}</div>
                                    <div class="jug-label">Vendidos</div>
                                </div>
                            </div>
                        </div>
                        @if ($dailyReconciliation['damage_cost'] > 0)
                            <div class="mt-2 text-danger small text-end fw-semibold">
                                Cobro por daños: {{ $signo }} {{ number_format($dailyReconciliation['damage_cost'], 2) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCION: VERIFICACION Y GESTION DE CAJAS ABIERTAS --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="ri-lock-unlock-line me-2 text-success"></i>Cajas Abiertas en Turno
                    <span class="badge bg-success-subtle text-success ms-2">{{ $openArchings->count() }} activa(s)</span>
                </h5>
                <small class="text-muted">Revise y verifique los movimientos de cada caja para realizar el cuadre y proceder al cierre.</small>
            </div>
            @if (! $assignedCash)
                <span class="badge bg-warning-subtle text-warning border px-3 py-2">
                    <i class="ri-alert-line me-1"></i>Sin caja física asignada a este usuario
                </span>
            @endif
        </div>
        <div class="card-body p-3">
            @if ($openArchings->isNotEmpty())
                <div class="row g-3">
                    @foreach ($openArchings as $item)
                        @php
                            $isMyCash = (int) $item->idusuario === (int) auth()->id();
                        @endphp
                        <div class="col-lg-6 col-xl-4">
                            <div class="open-cash-card p-3 h-100 {{ $isMyCash ? 'open-cash-highlight' : '' }}">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold mb-0 text-dark">{{ $item->cash?->descripcion ?? 'Caja' }}</h6>
                                            @if ($isMyCash)
                                                <span class="badge bg-success text-white" style="font-size: 0.72rem;">Mi caja</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            <i class="ri-store-2-line me-1"></i>{{ $item->warehouse?->descripcion ?? ($currentWarehouse?->descripcion ?? 'Almacén') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="ri-record-circle-line me-1"></i>Abierta
                                    </span>
                                </div>

                                <div class="bg-white rounded p-2 border mb-3">
                                    <div class="row g-2 text-center">
                                        <div class="col-6 border-end">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Responsable</small>
                                            <span class="fw-semibold text-dark text-truncate d-block" style="font-size: 0.85rem;" title="{{ $item->user?->nombres }}">
                                                {{ $item->user?->nombres ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Monto Apertura</small>
                                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                                                {{ $signo }} {{ number_format((float) $item->monto_inicial, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-muted text-center pt-2 mt-1 border-top" style="font-size: 0.78rem;">
                                        <i class="ri-time-line me-1"></i>Apertura: {{ optional($item->fecha_inicio)->format('d/m/Y') }}
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary w-50 btn-view-summary" data-id="{{ $item->id }}">
                                        <i class="ri-file-chart-line me-1"></i>Reconciliación
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger w-50 btn-prompt-close" data-id="{{ $item->id }}">
                                        <i class="ri-lock-2-line me-1"></i>Cerrar Caja
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <div class="mb-2">
                        <i class="ri-checkbox-circle-fill text-success" style="font-size: 2.5rem;"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No hay cajas abiertas en este momento</h6>
                    <p class="small text-muted mb-0">Todas las cajas registradas se encuentran cerradas o no se ha iniciado turno.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- SECCION: HISTORIAL DE ARQUEOS Y MOVIMIENTOS --}}
    <div class="card shadow-sm mb-4 arching-card border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="ri-history-line me-2 text-primary"></i>Historial de Arqueos
            </h5>
        </div>
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
