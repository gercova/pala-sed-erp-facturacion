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
                    </h1>
                    <div class="small text-muted mt-1">Gestión de pedidos de agua, asignación a choferes, rutas de entrega y liquidación en destino.</div>
                </div>
                <div class="col-auto mb-3 d-flex gap-2">
                    <a href="{{ route('admin.deliveries.qr') }}" class="btn btn-outline-primary waves-effect">
                        <i class="ri-qr-code-line align-middle me-1"></i> Generador QR
                    </a>
                    <button class="btn btn-primary waves-effect btn-create-order">
                        <i class="ri-add-circle-line align-middle me-1"></i> Nuevo Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <!-- Métricas del Día -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-start-lg border-start-warning h-100">
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
            <div class="card border-start-lg border-start-info h-100">
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
            <div class="card border-start-lg border-start-success h-100">
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
            <div class="card border-start-lg border-start-primary h-100">
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

    <!-- Filtros de búsqueda -->
    <div class="card mb-4">
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
                    <select id="filter_repartidor" class="form-select form-select-sm">
                        <option value="">[Todos los repartidores]</option>
                        @foreach($repartidores as $rep)
                            <option value="{{ $rep->id }}">{{ $rep->nombres }}</option>
                        @endforeach
                    </select>
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
    <div class="card mb-4">
        <div class="card-header fw-bold text-dark d-flex justify-content-between align-items-center">
            <span><i class="ri-list-check-2 me-1 align-middle text-primary"></i> Bandeja de Pedidos y Despacho</span>
            <span class="small text-muted">Se actualiza automáticamente con pedidos entrantes por QR</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
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
