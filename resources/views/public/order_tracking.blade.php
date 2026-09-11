<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Seguimiento de Pedido {{ $order->codigo_orden }} - {{ $business->razon_social ?? 'Distribuidora de Agua' }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6fa;
            color: #1f2937;
            padding-bottom: 3rem;
        }

        .tracking-card {
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid rgba(33, 40, 50, 0.12);
            box-shadow: 0 4px 16px rgba(18, 38, 63, 0.06);
            overflow: hidden;
        }

        .timeline-step {
            position: relative;
            padding-left: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .timeline-step:last-child {
            margin-bottom: 0;
        }

        .timeline-step::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 28px;
            bottom: -20px;
            width: 2px;
            background-color: #e5e7eb;
        }

        .timeline-step:last-child::before {
            display: none;
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            background-color: #e5e7eb;
            color: #6b7280;
        }

        .timeline-step.active .timeline-icon {
            background-color: #0061f2;
            color: #ffffff;
        }

        .timeline-step.completed .timeline-icon {
            background-color: #10b981;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <header class="bg-white border-bottom py-3 mb-4 text-center">
        <div class="container">
            <h5 class="fw-bold mb-0 text-dark">{{ $business->razon_social ?? 'DISTRIBUIDORA DE AGUA' }}</h5>
            <small class="text-muted">Estado de tu pedido en tiempo real</small>
        </div>
    </header>

    <div class="container" style="max-width: 580px;">

        <div class="tracking-card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-muted small">Número de Orden:</span>
                    <h4 class="fw-bold text-primary mb-0">{{ $order->codigo_orden }}</h4>
                </div>
                <div class="text-end">
                    @php
                        $badges = [
                            'pendiente' => '<span class="badge bg-warning text-dark fs-6">Recibido</span>',
                            'en_ruta' => '<span class="badge bg-info text-dark fs-6">En Camino</span>',
                            'entregado' => '<span class="badge bg-success text-white fs-6">Entregado</span>',
                            'cancelado' => '<span class="badge bg-danger text-white fs-6">Cancelado</span>',
                        ];
                    @endphp
                    {!! $badges[$order->estado] ?? '' !!}
                </div>
            </div>

            <hr class="my-3">

            <!-- Línea de Tiempo de Estado -->
            <div class="mb-4">
                <div class="timeline-step {{ in_array($order->estado, ['pendiente', 'en_ruta', 'entregado']) ? ($order->estado === 'pendiente' ? 'active' : 'completed') : '' }}">
                    <div class="timeline-icon"><i class="ri-file-list-3-line"></i></div>
                    <div class="fw-bold text-dark">1. Pedido Recibido</div>
                    <small class="text-muted">Tu pedido está registrado y siendo preparado en almacén.</small>
                </div>

                <div class="timeline-step {{ in_array($order->estado, ['en_ruta', 'entregado']) ? ($order->estado === 'en_ruta' ? 'active' : 'completed') : '' }}">
                    <div class="timeline-icon"><i class="ri-truck-line"></i></div>
                    <div class="fw-bold text-dark">2. En Ruta de Entrega</div>
                    <small class="text-muted">
                        @if($order->repartidor)
                            Repartidor asignado: <strong>{{ $order->repartidor->nombres }}</strong>
                        @else
                            El chofer asignado va en camino hacia tu dirección.
                        @endif
                    </small>
                </div>

                <div class="timeline-step {{ $order->estado === 'entregado' ? 'completed' : '' }}">
                    <div class="timeline-icon"><i class="ri-checkbox-circle-line"></i></div>
                    <div class="fw-bold text-dark">3. Entregado con Éxito</div>
                    <small class="text-muted">
                        @if($order->fecha_entrega)
                            Entregado el {{ $order->fecha_entrega->format('d/m/Y H:i') }}. ¡Gracias por tu preferencia!
                        @else
                            Entrega final y recepción de envases.
                        @endif
                    </small>
                </div>
            </div>

            <!-- Datos de Entrega -->
            <div class="bg-light p-3 rounded-3 mb-3">
                <h6 class="fw-bold text-dark mb-2">Detalles de Entrega</h6>
                <div class="small mb-1"><i class="ri-user-line text-primary me-1"></i> <strong>Cliente:</strong> {{ $order->cliente?->nombres }}</div>
                <div class="small mb-1"><i class="ri-map-pin-line text-primary me-1"></i> <strong>Dirección:</strong> {{ $order->direccion_entrega }} {{ $order->referencia ? '(' . $order->referencia . ')' : '' }}</div>
                <div class="small mb-1"><i class="ri-calendar-line text-primary me-1"></i> <strong>Fecha Programada:</strong> {{ $order->fecha_programada->format('d/m/Y') }} ({{ ucfirst($order->franja_horaria) }})</div>
                <div class="small"><i class="ri-wallet-3-line text-primary me-1"></i> <strong>Pago:</strong> {{ ucfirst($order->metodo_pago) }} ({{ ucfirst($order->estado_pago) }})</div>
            </div>

            <!-- Resumen de Productos -->
            <div class="mb-3">
                <h6 class="fw-bold text-dark mb-2">Productos Solicitados</h6>
                <ul class="list-group list-group-flush border rounded-3">
                    @foreach($order->items as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <span class="fw-semibold">{{ $item->descripcion }}</span>
                                <small class="d-block text-muted">{{ (int) $item->cantidad }} unid. x S/ {{ number_format($item->precio_unitario, 2) }}</small>
                            </div>
                            <span class="fw-bold text-dark">S/ {{ number_format($item->subtotal, 2) }}</span>
                        </li>
                    @endforeach
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 bg-light fw-bold">
                        <span>Total:</span>
                        <span class="text-primary fs-5">S/ {{ number_format($order->total, 2) }}</span>
                    </li>
                </ul>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('public.order.index') }}" class="btn btn-outline-primary">
                    <i class="ri-add-line me-1"></i> Realizar Otro Pedido
                </a>
            </div>
        </div>

    </div>

</body>
</html>
