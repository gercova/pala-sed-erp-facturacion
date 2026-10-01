@extends('admin.layout')

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="truck"></i></div>
                        Logística y Reparto de Bidones
                        @if($isRepartidor)
                            <span class="badge bg-primary-soft text-primary ms-2 fs-6">
                                <i class="ri-user-star-line me-1"></i> Mi Panel de Reparto
                            </span>
                        @endif
                    </h1>
                    <div class="small text-muted mt-1">Gestión de pedidos de agua, asignación a choferes, rutas de entrega y liquidación en destino.</div>
                </div>
                <div class="col-auto mb-3 d-flex gap-2">
                    <button class="btn btn-outline-info waves-effect" id="btn-open-containers-summary">
                        <i class="ri-archive-line align-middle me-1"></i> Control de Envases
                    </button>
                    @if(! $isRepartidor)
                        <a href="{{ route('admin.deliveries.qr') }}" class="btn btn-outline-primary waves-effect">
                            <i class="ri-qr-code-line align-middle me-1"></i> Generador QR
                        </a>
                        <button class="btn btn-primary waves-effect btn-create-order">
                            <i class="ri-add-circle-line align-middle me-1"></i> Nuevo Pedido
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <!-- Métricas de Pedidos del Día -->
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-start-lg border-start-warning h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Pedidos Pendientes</div>
                            <div class="h3 mb-0 text-warning" id="kpi-pendientes">{{ $kpis['pendientes'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-warning-soft text-warning">
                            <i class="ri-time-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-start-lg border-start-info h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">En Ruta de Entrega</div>
                            <div class="h3 mb-0 text-info" id="kpi-en-ruta">{{ $kpis['en_ruta'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-info-soft text-info">
                            <i class="ri-truck-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-start-lg border-start-success h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Entregados Hoy</div>
                            <div class="h3 mb-0 text-success" id="kpi-entregados">{{ $kpis['entregados_hoy'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-success-soft text-success">
                            <i class="ri-checkbox-circle-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-start-lg border-start-primary h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Cobrado Hoy (Delivery)</div>
                            <div class="h3 mb-0 text-primary" id="kpi-recaudado">S/ {{ number_format($kpis['recaudado_hoy'], 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-primary-soft text-primary">
                            <i class="ri-money-dollar-circle-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Seguimiento Global de Envases -->
    <div class="card mb-4 bg-light border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center g-2 text-center text-md-start">
                <div class="col-md-3">
                    <span class="small text-muted fw-semibold d-block">Envases en Clientes:</span>
                    <strong class="text-primary fs-6"><i class="ri-hand-coin-line me-1"></i> {{ $containerSummary['total_prestados'] }} prestados</strong>
                </div>
                <div class="col-md-3">
                    <span class="small text-muted fw-semibold d-block">Entregados Histórico:</span>
                    <strong class="text-success fs-6"><i class="ri-truck-line me-1"></i> {{ $containerSummary['total_entregados'] }} llenos</strong>
                </div>
                <div class="col-md-3">
                    <span class="small text-muted fw-semibold d-block">Retornados Intactos:</span>
                    <strong class="text-info fs-6"><i class="ri-recycle-line me-1"></i> {{ $containerSummary['total_devueltos'] }} vacíos</strong>
                </div>
                <div class="col-md-3">
                    <span class="small text-muted fw-semibold d-block">Dañados / Mermas:</span>
                    <strong class="text-danger fs-6"><i class="ri-error-warning-line me-1"></i> {{ $containerSummary['total_danados'] }} rotos</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-bold mb-1">Estado de Entrega</label>
                    <select id="filter_estado" class="form-select form-select-sm">
                        <option value="">[Todos los estados]</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_ruta">En Ruta</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold mb-1">Repartidor Asignado</label>
                    @if($isRepartidor)
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ auth()->user()->nombres }}" readonly>
                        <input type="hidden" id="filter_repartidor" value="{{ auth()->id() }}">
                    @else
                        <select id="filter_repartidor" class="form-select form-select-sm">
                            <option value="">[Todos los repartidores]</option>
                            @foreach($repartidores as $rep)
                                <option value="{{ $rep->id }}">{{ $rep->nombres }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold mb-1">Fecha Programada</label>
                    <input type="date" id="filter_fecha" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 d-flex gap-1 align-items-end mt-3 mt-md-0">
                    <button type="button" id="btn-filter" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="ri-filter-3-line me-1"></i> Filtrar
                    </button>
                    <button type="button" id="btn-reset-filter" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Principal de Pedidos -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header fw-bold text-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <span><i class="ri-list-check-2 me-1 align-middle text-primary"></i> Bandeja de Pedidos y Despacho</span>
                <span class="small text-muted ms-2 fw-normal d-none d-md-inline">| Monitoreo de ruta y liquidación</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="badge bg-success-soft text-success px-2 py-1 d-flex align-items-center gap-1" id="live-polling-badge" title="Actualización automática de pedidos cada 15 segundos">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 0.65rem; height: 0.65rem;"></span>
                    <span id="polling-status-text">En vivo (15s)</span>
                </div>
                <button type="button" id="btn-toggle-polling" class="btn btn-sm btn-outline-secondary shadow-none" title="Pausar / Reanudar actualización automática">
                    <i class="ri-pause-line" id="polling-toggle-icon"></i>
                </button>
                <button type="button" id="btn-refresh-table" class="btn btn-sm btn-outline-primary shadow-none" title="Refrescar datos de la tabla">
                    <i class="ri-refresh-line me-1"></i> Refrescar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="min-height: 320px;">
                <table id="table-deliveries" class="table table-hover table-sm align-middle w-100">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Dirección / Ref.</th>
                            <th>Bidones</th>
                            <th>Repartidor</th>
                            <th>Origen</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th class="text-center" width="12%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.deliveries.modals')

@endsection

@section('scripts')
    @include('admin.deliveries.js-deliveries')
@endsection
