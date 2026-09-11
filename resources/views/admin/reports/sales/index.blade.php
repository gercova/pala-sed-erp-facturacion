@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="bar-chart-2"></i></div>
                        Reporte de ventas
                    </h1>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="card mb-4">
        <div class="card-body">
            <form id="formReport" class="row gx-3 align-items-end">
                <div class="col-md-10">
                    <label class="small mb-1">Rango de Fechas</label>
                    <div class="input-group">
                        <input type="date" id="start_date" class="form-control">
                        <span class="input-group-text">a</span>
                        <input type="date" id="end_date" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-refresh-line me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card border-start-lg border-start-primary h-100">
                <div class="card-body">
                    <div class="small text-muted">Ventas Totales</div>
                    <div class="h3" id="kpi-total-ventas">$ 0.00</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card border-start-lg border-start-success h-100">
                <div class="card-body">
                    <div class="small text-muted">Cantidad de Órdenes</div>
                    <div class="h3" id="kpi-cantidad-ventas">0</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card border-start-lg border-start-info h-100">
                <div class="card-body">
                    <div class="small text-muted">Ticket Promedio</div>
                    <div class="h3" id="kpi-ticket-promedio">$ 0.00</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white">Tendencia de Ventas Diarias</div>
        <div class="card-body">
            <div style="height: 300px;"><canvas id="salesChart"></canvas></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover w-100" id="salesReportTable">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Cant. Ventas</th>
                        <th>Subtotal</th>
                        <th>Impuestos</th>
                        <th>Total</th>
                        <th>Ticket Promedio</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('admin.reports.sales.js')
@endsection