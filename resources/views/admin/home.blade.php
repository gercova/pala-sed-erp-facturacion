@extends('admin.layout')
@section('styles')
@endsection
@section('content')
    <div class="container-xl px-4 mt-5">
        <div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-4">
            <div class="me-4 mb-3 mb-sm-0">
                <h1 class="mb-0">Dashboard</h1>
                <div class="small">
                    <span
                        class="fw-500 text-primary">{{ ucfirst(\Carbon\Carbon::now()->locale('es')->translatedFormat('l')) }}</span>
                    · {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y · h:i A') }}
                </div>
            </div>
        </div>
         <div class="row">
            <div class="col-lg-4 mb-4">
                <!-- Illustration card example-->
                <div class="card mb-4">
                    <div class="card-body text-center p-5">
                        <img class="img-fluid mb-5" src="{{ asset('assets/img/illustrations/data-report.svg') }}">
                        <h4>Generaci&oacute;n de Reportes</h4>
                        <p class="mb-4">Consulta los informes detallados de ventas, stock y &oacute;rdenes pendientes.
                            Optimiza tu negocio y toma decisiones estrat&eacute;gicas en tiempo real.</p>
                        <a class="btn btn-primary p-3" href="{{ route('report.sales.index') }}">Ver Reportes</a>
                    </div>
                </div>
                <!-- Report summary card example-->
                <div class="card mb-4">
                    <div class="card-header">Reportes de Negocio</div>
                    <div class="list-group list-group-flush small">
                        <a class="list-group-item list-group-item-action" href="{{ route('report.sales.index') }}">
                            <i class="fas fa-dollar-sign fa-fw text-blue me-2"></i>
                            Reporte de Ventas
                        </a>
                        <a class="list-group-item list-group-item-action"
                            href="{{ route('report.sales.by_product.index') }}">
                            <i class="fas fa-box fa-fw text-purple me-2"></i>
                            Reporte de Productos
                        </a>
                        {{-- <a class="list-group-item list-group-item-action" href="javascript:void(0)">
                        <i class="fas fa-exchange-alt fa-fw text-green me-2"></i>
                        Órdenes de Transferencia
                    </a> --}}
                        <a class="list-group-item list-group-item-action" href="{{ route('report.payments.index') }}">
                            <i class="fas fa-chart-line fa-fw text-yellow me-2"></i>
                            M&eacute;todos de Pago
                        </a>
                        {{-- <a class="list-group-item list-group-item-action" href="javascript:void(0)">
                        <i class="fas fa-users fa-fw text-pink me-2"></i>
                        Reporte de Clientes
                    </a> --}}
                    </div>
                    <div class="card-footer position-relative border-top-0">
                        <a class="stretched-link" href="{{ route('report.sales.index') }}">
                            <div class="text-xs d-flex align-items-center justify-content-between">
                                Ver m&aacute;s reportes
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Progress card example-->
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #224abe, #1e3a8a);">
                    <div class="card-body">
                        <h5 class="text-white-50">Resumen de Presupuesto</h5>
                        <div class="mb-4">
                            <span
                                class="display-4 text-white">{{ $signo . number_format($presupuestoAnual, 2, '.', '') ?? $signo . '0.00' }}</span>
                            <span class="text-white-50">anual</span>
                        </div>
                        <div class="progress bg-white-25 rounded-pill" style="height: 0.5rem">
                            <div class="progress bg-white-25 rounded-pill" style="height: 0.5rem">
                                <div class="progress-bar bg-warning rounded-pill" role="progressbar"
                                    style="width: {{ $porcentajePresupuesto }}%;"
                                    aria-valuenow="{{ $porcentajePresupuesto }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-8 mb-4">
                <!-- Area chart example-->
                <div class="card mb-4">
                    <div class="card-header">Resumen de Ingresos</div>
                    <div class="card-body">
                        <div class="chart-area"><canvas id="myAreaChart" width="100%" height="30"></canvas></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <!-- Bar chart example-->
                        <div class="card h-100">
                            <div class="card-header">Informe de Ventas</div>
                            <div class="card-body d-flex flex-column justify-content-center">
                                <div class="chart-bar"><canvas id="myBarChart" width="100%" height="30"></canvas>
                                </div>
                            </div>
                            <div class="card-footer position-relative">
                                <a class="stretched-link" href="{{ route('report.sales.index') }}">
                                    <div class="text-xs d-flex align-items-center justify-content-between">
                                        Ver m&aacute;s reportes
                                        <i class="fas fa-long-arrow-alt-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <!-- Pie chart example-->
                        <div class="card h-100">
                            <div class="card-header">Ventas por M&eacute;todo de Pago</div>
                            <div class="card-body">
                                <div class="chart-pie mb-4"><canvas id="myPieChart" width="100%"
                                        height="50"></canvas></div>
                                <div class="listaMetodosPago list-group list-group-flush"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Custom page header alternative example-->
        
        
        <div class="row">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted fw-semibold small text-uppercase mb-1 ls-wide">Ingresos del día</p>
                                <h3 class="fw-bold mb-0 text-dark">
                                    <span
                                        class="text-primary me-1">{{ $signo }}</span>{{ number_format($totalVentas, 2) }}
                                </h3>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-cash-register fa-lg text-primary"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-success bg-opacity-10 text-success small">
                                <i class="fas fa-arrow-up me-1"></i> Actualizado
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted fw-semibold small text-uppercase mb-1 ls-wide">&Oacute;rdenes
                                    generadas
                                </p>
                                <h3 class="fw-bold mb-0 text-dark">{{ $totalTransferencias }}</h3>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fas fa-exchange-alt fa-lg text-info"></i>
                            </div>
                        </div>

                        <div class="mt-3">
                            <span class="badge rounded-pill bg-info text-dark bg-opacity-10 fw-normal">
                                <i class="fas fa-info-circle me-1"></i> {{ $totalTransferencias }} registradas desde las
                                00:00
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted fw-semibold small text-uppercase mb-1 ls-wide">Tasa de conversión</p>
                                <h3 class="fw-bold mb-0 text-dark">
                                    {{ $totalStock > 0 ? round(($ventasHoy / $totalStock) * 100, 2) : 0 }}%
                                </h3>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="fas fa-percentage fa-lg text-warning"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                style="width: {{ $totalStock > 0 ? ($ventasHoy / $totalStock) * 100 : 0 }}%"
                                aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen -->
        <div class="card custom-card pro-card mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <h2 class="fw-bold text-dark mb-1">Resumen</h2>
                            <p class="text-muted mb-0">Estado general del día.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card border-0 h-100" style="background: #f7f9fc; border-radius: 14px;">
                                    <div class="card-body p-3 p-lg-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="p-2 bg-white rounded-3 shadow-sm me-3">
                                                <i class="fas fa-shopping-cart text-primary fa-lg"></i>
                                            </div>
                                            <small class="text-primary fw-bold text-uppercase">Ventas</small>
                                        </div>
                                        <div class="h3 fw-black text-dark mb-0 ms-1">{{ $ventasHoy }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-0 h-100" style="background: #f7f9fc; border-radius: 14px;">
                                    <div class="card-body p-3 p-lg-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="p-2 bg-white rounded-3 shadow-sm me-3">
                                                <i class="fas fa-boxes text-success fa-lg"></i>
                                            </div>
                                            <small class="text-success fw-bold text-uppercase">Stock</small>
                                        </div>
                                        <div class="h3 fw-black text-dark mb-0 ms-1">{{ $totalStock }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-0 h-100" style="background: #f7f9fc; border-radius: 14px;">
                                    <div class="card-body p-3 p-lg-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="p-2 bg-white rounded-3 shadow-sm me-3">
                                                <i class="fas fa-truck-loading text-danger fa-lg"></i>
                                            </div>
                                            <small class="text-danger fw-bold text-uppercase">Ordenes</small>
                                        </div>
                                        <div class="h3 fw-black text-dark mb-0 ms-1">{{ $ordenesPendientes }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-2">
                            <a href="{{ route('admin.sale_notes') }}"
                                class="btn btn-primary px-4 shadow-sm fw-bold">
                                Explorar Detalles <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-4 d-none d-lg-block text-center">
                        <img src="{{ asset('assets/img/illustrations/statistics.svg') }}" alt="Estadísticas"
                            class="img-fluid"
                            style="max-height: 220px; filter: drop-shadow(0px 14px 18px rgba(0,0,0,0.06));">
                    </div>
                </div>
            </div>
        </div>


       
    </div>
@endsection
@section('scripts')
@endsection