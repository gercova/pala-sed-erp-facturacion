@extends('admin.layout')

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="repeat"></i></div>
                        Control de Envases Retornables y Comodatos
                    </h1>
                    <div class="small text-muted mt-1">Seguimiento de bidones en poder de clientes, control de devoluciones (aptos vs dañados/rotos) y mermas.</div>
                </div>
                <div class="col-auto mb-3">
                    <button type="button" class="btn btn-primary waves-effect btn-direct-return">
                        <i class="ri-arrow-go-back-line align-middle me-1"></i> Registrar Devolución Directa
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <!-- Indicadores de Envases -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-start-lg border-start-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Envases en Clientes</div>
                            <div class="h3 mb-0 text-primary">{{ $kpis['total_envases_clientes'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-primary-soft text-primary">
                            <i class="ri-cup-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-start-lg border-start-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Clientes con Saldo</div>
                            <div class="h3 mb-0 text-warning">{{ $kpis['clientes_con_saldo'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-warning-soft text-warning">
                            <i class="ri-user-shared-line fs-4"></i>
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
                            <div class="small text-muted fw-bold text-uppercase">Devueltos Intactos (Mes)</div>
                            <div class="h3 mb-0 text-success">{{ $kpis['devueltos_intactos_mes'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-success-soft text-success">
                            <i class="ri-checkbox-circle-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-start-lg border-start-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Devueltos Dañados (Mes)</div>
                            <div class="h3 mb-0 text-danger">{{ $kpis['devueltos_danados_mes'] }}</div>
                        </div>
                        <div class="rounded-3 p-3 bg-danger-soft text-danger">
                            <i class="ri-error-warning-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación por pestañas estándar del template -->
    <nav class="nav nav-borders mb-4" role="tablist">
        <a class="nav-link active ms-0" data-bs-toggle="tab" href="#tab-balances" role="tab" aria-selected="true">
            <i class="ri-user-line me-1 align-middle"></i> Saldos de Envases por Cliente
        </a>
        <a class="nav-link" data-bs-toggle="tab" href="#tab-movements" role="tab" aria-selected="false">
            <i class="ri-history-line me-1 align-middle"></i> Historial General de Movimientos
        </a>
    </nav>

    <div class="tab-content">
        <!-- Pestaña 1: Saldos por Cliente -->
        <div class="tab-pane fade show active" id="tab-balances" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header fw-bold text-dark d-flex justify-content-between align-items-center">
                    <span><i class="ri-file-user-line me-1 text-primary"></i> Cuenta Corriente de Envases</span>
                    <span class="small text-muted">Saldo positivo = Bidones en poder del cliente</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-jug-balances" class="table table-hover table-sm align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Saldo de Envases</th>
                                    <th class="text-center" width="18%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Historial General de Movimientos -->
        <div class="tab-pane fade" id="tab-movements" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header fw-bold text-dark">
                    <i class="ri-exchange-line me-1 text-primary"></i> Kardex de Movimientos de Envases
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-jug-movements" class="table table-hover table-sm align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Tipo Movimiento</th>
                                    <th>Detalle Envases</th>
                                    <th>Saldo Ant.</th>
                                    <th>Nuevo Saldo</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.jug_movements.modals')

@endsection

@section('scripts')
    @include('admin.jug_movements.js-jug-movements')
@endsection
