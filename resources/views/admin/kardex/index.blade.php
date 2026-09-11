@extends('admin.layout')

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="activity"></i></div>
                        Kardex
                    </h1>
                </div>
                <div class="col-auto mb-3">
                    <span class="badge bg-primary-soft text-primary">{{ $currentWarehouse?->descripcion ?: 'Todos los almacenes' }}</span>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card custom-card pro-card h-100"><div class="card-body"><div class="small text-muted">Movimientos</div><div class="h3 mb-0">{{ $summary['movement_count'] }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card pro-card h-100"><div class="card-body"><div class="small text-muted">Entradas</div><div class="h3 mb-0 text-success">{{ number_format($summary['entries'], 2) }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card pro-card h-100"><div class="card-body"><div class="small text-muted">Salidas</div><div class="h3 mb-0 text-danger">{{ number_format($summary['exits'], 2) }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card pro-card h-100"><div class="card-body"><div class="small text-muted">Saldo acumulado</div><div class="h3 mb-0">{{ number_format($summary['closing_balance'], 2) }}</div></div></div>
        </div>
    </div>

    <div class="card custom-card pro-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kardex') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Producto</label>
                    <select name="product_id" class="form-select">
                        <option value="0">Todos</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ (int) $filters['product_id'] === (int) $product->id ? 'selected' : '' }}>{{ $product->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="movement_type" class="form-select">
                        <option value="">Todos</option>
                        <option value="buy" {{ $filters['movement_type'] === 'buy' ? 'selected' : '' }}>Compra</option>
                        <option value="billing" {{ $filters['movement_type'] === 'billing' ? 'selected' : '' }}>Boleta / Factura</option>
                        <option value="sale_note" {{ $filters['movement_type'] === 'sale_note' ? 'selected' : '' }}>Nota de venta</option>
                        <option value="transfer_in" {{ $filters['movement_type'] === 'transfer_in' ? 'selected' : '' }}>Traslado ingreso</option>
                        <option value="transfer_out" {{ $filters['movement_type'] === 'transfer_out' ? 'selected' : '' }}>Traslado salida</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    <a href="{{ route('admin.kardex') }}" class="btn btn-light border w-100">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card custom-card pro-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Movimiento</th>
                            <th>Documento</th>
                            <th>Producto</th>
                            <th>Almac&eacute;n</th>
                            <th class="text-end">Entrada</th>
                            <th class="text-end">Salida</th>
                            <th class="text-end">Saldo</th>
                            <th class="text-end">Costo/U.</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $row->movement_label }}</span></td>
                                <td class="fw-semibold">{{ $row->documento }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ $row->warehouse_name ?: '-' }}</td>
                                <td class="text-end text-success">{{ $row->entrada > 0 ? number_format((float) $row->entrada, 2) : '-' }}</td>
                                <td class="text-end text-danger">{{ $row->salida > 0 ? number_format((float) $row->salida, 2) : '-' }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $row->saldo, 2) }}</td>
                                <td class="text-end">{{ $signo }} {{ number_format((float) $row->costo_unitario, 2) }}</td>
                                <td class="text-end">{{ $signo }} {{ number_format((float) $row->total_movimiento, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No se encontraron movimientos para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
