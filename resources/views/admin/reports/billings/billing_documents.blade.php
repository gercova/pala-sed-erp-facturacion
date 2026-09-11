@extends('admin.layout')

@section('styles')
    @include('admin.reports.billings._styles')
@endsection

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="file-text"></i></div>
                        {{ $title }}
                    </h1>
                    <div class="small text-muted mt-1">{{ $subtitle }}</div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="card shadow-sm mb-4 billing-report-card">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-1">Filtro del reporte</h5>
                <p class="mb-0 billing-report-subtitle">
                    Almacen activo: <strong>{{ $currentWarehouse?->descripcion ?? 'No seleccionado' }}</strong>
                </p>
            </div>
            <div class="d-inline-flex gap-2">
                <button type="button" class="btn btn-outline-danger d-flex align-items-center report-export-btn" id="btn_export_pdf">
                    <i class="ri-file-pdf-line me-1"></i>
                    <span class="report-export-label">Exportar PDF</span>
                </button>
                <button type="button" class="btn btn-outline-success d-flex align-items-center report-export-btn" id="btn_export_excel">
                    <i class="ri-file-excel-2-line me-1"></i>
                    <span class="report-export-label">Exportar Excel</span>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="billing-report-panel rounded-4 p-3 mb-4">
                <div class="row g-3 billing-report-filters">
                    <div class="col-md-6 col-xl-2">
                        <label class="form-label">Fecha desde</label>
                        <input type="date" class="form-control" id="filter_date_from" value="{{ $defaultDateFrom }}">
                    </div>
                    <div class="col-md-6 col-xl-2">
                        <label class="form-label">Fecha hasta</label>
                        <input type="date" class="form-control" id="filter_date_to" value="{{ $defaultDateTo }}">
                    </div>
                    <div class="col-md-6 col-xl-2">
                        <label class="form-label">Comprobante</label>
                        <select id="filter_document_type" class="form-select">
                            <option value="">Todos</option>
                            <option value="03">Boleta</option>
                            <option value="01">Factura</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-2">
                        <label class="form-label">Estado</label>
                        <select id="filter_status" class="form-select">
                            <option value="">Todos</option>
                            <option value="vigente">Vigente</option>
                            <option value="anulado">Anulado</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-2">
                        <label class="form-label">Estado SUNAT</label>
                        <select id="filter_sunat_status" class="form-select">
                            <option value="">Todos</option>
                            <option value="accepted">Aceptado</option>
                            <option value="pending">Pendiente</option>
                            <option value="rejected">Rechazado</option>
                            <option value="annulled">Anulado</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-2">
                        <label class="form-label">Pago</label>
                        <select id="filter_pay_mode" class="form-select">
                            <option value="">Todos</option>
                            <option value="Contado">Contado</option>
                            <option value="Credito">Credito</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 billing-report-filters mt-0">
                    <div class="col-md-6 col-xl-4">
                        <label class="form-label">Cliente</label>
                        <select id="filter_client_id" class="form-select billing-report-client-select">
                            <option value="">Documento - nombre del cliente</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ trim(($client->nro_documento ?: 'Sin documento') . ' - ' . $client->nombres) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label">Serie o correlativo</label>
                        <input type="text" class="form-control" id="filter_series" placeholder="B001-00000025">
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label">Usuario</label>
                        <select id="filter_user" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->nombres }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-2 d-flex flex-column justify-content-end">
                        <button type="button" class="btn billing-report-clear-btn" id="btn_reset_filters">
                            <i class="ri-refresh-line me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <div class="report-summary-grid">
                <div class="report-summary-item"><small>Documentos</small><strong id="summary_count">0</strong></div>
                <div class="report-summary-item"><small>Gravada</small><strong id="summary_gravada">{{ $signo }} 0.00</strong></div>
                <div class="report-summary-item"><small>IGV</small><strong id="summary_igv">{{ $signo }} 0.00</strong></div>
                <div class="report-summary-item"><small>Total</small><strong id="summary_total">{{ $signo }} 0.00</strong></div>
            </div>

            <div class="table-responsive">
                <table id="billingDocumentsTable" class="table table-striped table-center table-nowrap mb-0 report-table">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Comprobante</th>
                            <th>Cliente</th>
                            <th class="text-center">Gravada</th>
                            <th class="text-center">IGV</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">SUNAT</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Pago</th>
                            <th class="text-center">Usuario</th>
                            <th class="text-center">Almacen</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @php
        $tableId = '#billingDocumentsTable';
        $dataRoute = route('report.billings.billing_documents.data');
        $pdfRoute = route('report.billings.billing_documents.pdf');
        $excelRoute = route('report.billings.billing_documents.excel');
        $datatableColumns = [
            ['data' => 'fecha_emision', 'name' => 'billings.fecha_emision', 'className' => 'text-center'],
            ['data' => 'comprobante', 'name' => 'comprobante', 'className' => 'text-center'],
            ['data' => 'cliente_info', 'name' => 'clients.nombres'],
            ['data' => 'gravada', 'name' => 'billings.gravada', 'className' => 'text-center'],
            ['data' => 'igv', 'name' => 'billings.igv', 'className' => 'text-center'],
            ['data' => 'total', 'name' => 'billings.total', 'className' => 'text-center'],
            ['data' => 'sunat_badge', 'name' => 'sunat_badge', 'className' => 'text-center', 'orderable' => false, 'searchable' => false],
            ['data' => 'estado_badge', 'name' => 'estado_badge', 'className' => 'text-center', 'orderable' => false, 'searchable' => false],
            ['data' => 'sunat_forma_pago', 'name' => 'billings.sunat_forma_pago', 'className' => 'text-center'],
            ['data' => 'usuario', 'name' => 'users.nombres', 'className' => 'text-center'],
            ['data' => 'almacen', 'name' => 'warehouses.descripcion', 'className' => 'text-center'],
        ];
    @endphp
    @include('admin.reports.billings._scripts')
@endsection
