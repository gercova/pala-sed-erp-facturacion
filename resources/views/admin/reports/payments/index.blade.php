@extends('admin.layout')

@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="credit-card"></i></div>
                        Reporte de Ventas por Método de Pago
                    </h1>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-body">
                    <form id="formReport">
                        <div class="row">
                            <div class="col-md-5">
                                <label for="start_date" class="form-label">Fecha inicio</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <label for="end_date" class="form-label">Fecha fin</label>
                                <input type="date" id="end_date" name="end_date" class="form-control">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Generar</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4">
                        <table class="table table-sm table-striped" id="salesReportTable">
                            <thead>
                                <tr>
                                    <th class="text-center">Método de Pago</th>
                                    <th class="text-center">Cantidad de Transacciones</th>
                                    <th class="text-center">Total Recaudado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Datos dinámicos -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @include('admin.reports.payments.js')
@endsection
