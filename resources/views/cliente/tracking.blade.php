@extends('cliente.layout')
@section('title', 'Seguimiento — ' . $order->codigo_orden)
@section('meta_description', 'Seguimiento en tiempo real de tu pedido ' . $order->codigo_orden)
@section('extra-styles')
    <link rel="stylesheet" href="{{ asset('css/tracking-client.css') }}">
@endsection

@section('content')
    @php
        $estado = $order->estado;
        $cancelado = $estado === 'cancelado';
        $esEntregado = $estado === 'entregado';
        $esEnRuta = in_array($estado, ['en_ruta', 'en_camino']);
        $esPendiente = $estado === 'pendiente';

        // Calculation for progress bar line
        $progressPercent = '0%';
        if ($esPendiente) {
            $progressPercent = '20%';
        } elseif ($esEnRuta) {
            $progressPercent = '50%';
        } elseif ($esEntregado) {
            $progressPercent = '80%';
        }
    @endphp

    <div class="page-header"
        style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem; margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.6rem; font-weight:700; color:#1e293b; margin:0 0 .25rem 0;">Seguimiento del Pedido</h1>
            <p style="margin:0; font-size:.9rem; color:#64748b;">
                Orden: <span style="font-family:monospace; font-weight:700; color:#0b5ed7; font-size:1.05rem;">{{ $order->codigo_orden }}</span>
                — Registrado el {{ $order->created_at ? $order->created_at->format('d/m/Y h:i A') : '—' }}
            </p>
        </div>
        <div style="display:flex; gap:.5rem;">
            <a href="{{ route('cliente.dashboard') }}"
                style="text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1rem; border:1.5px solid #e2e8f0; border-radius:10px; font-size:.85rem; font-weight:600; color:#475569; background:#fff; transition:all .15s;"
                onmouseover="this.style.borderColor='#94a3b8'" onmouseout="this.style.borderColor='#e2e8f0'">
                <i class="ri-arrow-left-line"></i> Mis pedidos
            </a>
            <a href="{{ route('cliente.order') }}"
                style="text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1rem; background:#0b5ed7; color:#fff; border-radius:10px; font-size:.85rem; font-weight:600; transition:all .15s;"
                onmouseover="this.style.background='#0a4fc4'" onmouseout="this.style.background='#0b5ed7'">
                <i class="ri-add-circle-line"></i> Nuevo pedido
            </a>
        </div>
    </div>

    {{-- Banner de Estado Actual --}}
    @if ($cancelado)
        <div class="status-banner cancelado">
            <div class="status-banner-icon">
                <i class="ri-close-circle-fill"></i>
            </div>
            <div class="status-banner-text">
                <h5>Pedido Cancelado</h5>
                <p>Este pedido fue cancelado. Si consideras que se trata de un error o deseas reprogramarlo, contáctanos.</p>
            </div>
        </div>
    @elseif($esEntregado)
        <div class="status-banner entregado">
            <div class="status-banner-icon">
                <i class="ri-checkbox-circle-fill"></i>
            </div>
            <div class="status-banner-text">
                <h5>¡Pedido Entregado con Éxito!</h5>
                <p>Tu pedido fue entregado y tus bidones fueron recibidos. ¡Muchas gracias por tu compra!</p>
            </div>
        </div>
    @elseif($esEnRuta)
        <div class="status-banner en_ruta">
            <div class="status-banner-icon">
                <i class="ri-truck-fill"></i>
            </div>
            <div class="status-banner-text">
                <h5>¡Tu pedido va en camino!</h5>
                <p>El repartidor {{ $order->repartidor?->nombres ? '('.$order->repartidor->nombres.')' : '' }} está en ruta hacia tu dirección de entrega.</p>
            </div>
        </div>
    @else
        <div class="status-banner pendiente">
            <div class="status-banner-icon">
                <i class="ri-time-fill"></i>
            </div>
            <div class="status-banner-text">
                <h5>Pedido Recibido — En Preparación</h5>
                <p>Tu pedido está confirmado en nuestro sistema. Estamos alistando los bidones y asignando chofer para salir a reparto.</p>
            </div>
        </div>
    @endif

    <div style="display:grid; grid-template-columns:1fr 340px; gap:1.5rem; align-items:start;">

        {{-- ── Columna Izquierda: Stepper y Productos ── --}}
        <div>

            {{-- Card del Stepper de Estado --}}
            <div class="p-card" style="margin-bottom:1.5rem;">
                <div class="p-card-header" style="margin-bottom:1.25rem;">
                    <i class="ri-route-line"></i> Estado del Pedido
                </div>

                @if (!$cancelado)
                    <div class="order-stepper-wrapper">
                        <div class="order-stepper">
                            <div class="order-stepper-line-bg"></div>
                            <div class="order-stepper-line-fill" style="width: {{ $progressPercent }};"></div>

                            {{-- Paso 1: Pedido Recibido --}}
                            <div class="stepper-step done">
                                <div class="step-circle">
                                    <i class="ri-check-line"></i>
                                </div>
                                <div class="step-info">
                                    <div class="step-title">1. Pedido Recibido</div>
                                    <span class="step-badge done"><i class="ri-check-line"></i> Confirmado</span>
                                    <p class="step-desc">
                                        {{ $order->created_at ? $order->created_at->format('d/m/Y h:i A') : 'Registrado en sistema' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Paso 2: En Camino / En Preparación --}}
                            @php
                                $step2Class = $esEntregado ? 'done' : ($esEnRuta ? 'active' : ($esPendiente ? 'active' : 'pending'));
                            @endphp
                            <div class="stepper-step {{ $step2Class }}">
                                <div class="step-circle">
                                    @if ($esEntregado)
                                        <i class="ri-check-line"></i>
                                    @elseif($esEnRuta)
                                        <i class="ri-truck-line"></i>
                                    @else
                                        <i class="ri-time-line"></i>
                                    @endif
                                </div>
                                <div class="step-info">
                                    <div class="step-title">2. {{ $esEnRuta ? 'En Camino' : ($esEntregado ? 'En Ruta' : 'En Preparación') }}</div>
                                    @if ($esEntregado)
                                        <span class="step-badge done"><i class="ri-check-line"></i> Despachado</span>
                                        <p class="step-desc">Ruta completada</p>
                                    @elseif($esEnRuta)
                                        <span class="step-badge active"><i class="ri-truck-line"></i> En ruta</span>
                                        <p class="step-desc">Rumbo a tu domicilio</p>
                                    @else
                                        <span class="step-badge active"><i class="ri-time-line"></i> Alistando</span>
                                        <p class="step-desc">Asignando repartidor</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Paso 3: Entregado --}}
                            <div class="stepper-step {{ $esEntregado ? 'done' : 'pending' }}">
                                <div class="step-circle">
                                    @if ($esEntregado)
                                        <i class="ri-checkbox-circle-fill"></i>
                                    @else
                                        <i class="ri-home-smile-line"></i>
                                    @endif
                                </div>
                                <div class="step-info">
                                    <div class="step-title">3. Entregado</div>
                                    @if ($esEntregado)
                                        <span class="step-badge done"><i class="ri-check-line"></i> Entregado</span>
                                        <p class="step-desc">
                                            {{ $order->fecha_entrega ? \Carbon\Carbon::parse($order->fecha_entrega)->format('d/m/Y h:i A') : 'Entregado con éxito' }}
                                        </p>
                                    @else
                                        <span class="step-badge pending">Pendiente</span>
                                        <p class="step-desc">Recepción de bidones</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Repartidor Asignado (si aplica) --}}
                    @if ($order->repartidor)
                        <div class="driver-card">
                            <div class="driver-info">
                                <div class="driver-avatar">
                                    {{ strtoupper(substr($order->repartidor->nombres ?? 'R', 0, 1)) }}
                                </div>
                                <div>
                                    <span style="font-size:.74rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.04em; display:block;">
                                        Repartidor Asignado
                                    </span>
                                    <b style="font-size:.95rem; color:#1e293b;">{{ $order->repartidor->nombres }}</b>
                                    @if (!empty($order->repartidor->user))
                                        <span style="font-size:.8rem; color:#64748b; margin-left:.5rem;">
                                            <i class="ri-phone-line"></i> {{ $order->repartidor->user }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.78rem;">
                                <i class="ri-e-bike-2-line"></i> En despacho
                            </span>
                        </div>
                    @endif
                @else
                    <div style="text-align:center; padding:2.5rem 1rem; color:#94a3b8;">
                        <i class="ri-close-circle-line" style="font-size:3.5rem; color:#fca5a5; display:block; margin-bottom:.5rem;"></i>
                        <h4 style="color:#dc2626; font-weight:700; margin:0 0 .25rem 0;">Pedido Cancelado</h4>
                        <p style="color:#64748b; font-size:.85rem; margin:0;">Este pedido no será entregado.</p>
                    </div>
                @endif

                {{-- Notas del pedido si existen --}}
                @if ($order->notas)
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:.85rem 1.1rem; margin-top:1rem;">
                        <div style="font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#92400e; margin-bottom:.25rem; display:flex; align-items:center; gap:.35rem;">
                            <i class="ri-information-line"></i> Notas del Pedido
                        </div>
                        <p style="font-size:.85rem; color:#78350f; margin:0;">{{ $order->notas }}</p>
                    </div>
                @endif
            </div>

            {{-- Card de Productos en la Orden --}}
            <div class="p-card">
                <div class="p-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="ri-shopping-basket-2-line"></i> Productos del Pedido</span>
                    @if ($order->bidones_a_entregar > 0)
                        <span class="badge bg-primary text-white" style="font-size:.75rem;">
                            <i class="ri-water-flash-line"></i> {{ $order->bidones_a_entregar }} bidon(es)
                        </span>
                    @endif
                </div>

                @if ($order->items && $order->items->count() > 0)
                    <div style="overflow-x:auto;">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width:50%;">Producto</th>
                                    <th style="text-align:center; width:15%;">Cant.</th>
                                    <th style="text-align:right; width:15%;">P. Unit.</th>
                                    <th style="text-align:right; width:20%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <b style="color:#1e293b;">{{ $item->descripcion }}</b>
                                            @if ($item->descuento > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.7rem; margin-left:.35rem;">
                                                    🎁 ¡Gratis fidelidad!
                                                </span>
                                            @endif
                                        </td>
                                        <td style="text-align:center; font-weight:600;">
                                            {{ (int) $item->cantidad }}
                                        </td>
                                        <td style="text-align:right; color:#64748b;">
                                            S/ {{ number_format($item->precio_unitario, 2) }}
                                        </td>
                                        <td style="text-align:right; font-weight:700; color:#0b5ed7;">
                                            S/ {{ number_format($item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="color:#94a3b8; font-size:.85rem; margin:1rem 0 0 0; text-align:center;">
                        No hay productos registrados en el detalle.
                    </p>
                @endif
            </div>

        </div>

        {{-- ── Columna Derecha: Datos de Entrega y Pago ── --}}
        <div style="display:flex; flex-direction:column; gap:1.25rem;">

            {{-- Datos de Entrega ── --}}
            <div class="p-card">
                <div class="p-card-header"><i class="ri-map-pin-2-line"></i> Datos de Entrega</div>

                <div class="detail-row">
                    <span class="detail-label">Dirección</span>
                    <span class="detail-value">{{ $order->direccion_entrega }}</span>
                </div>

                @if ($order->referencia)
                    <div class="detail-row">
                        <span class="detail-label">Referencia</span>
                        <span class="detail-value">{{ $order->referencia }}</span>
                    </div>
                @endif

                @if ($order->coordenadas)
                    <div class="detail-row">
                        <span class="detail-label">GPS</span>
                        <span class="detail-value" style="font-family:monospace; font-size:.8rem; color:#0b5ed7;">
                            {{ $order->coordenadas }}
                        </span>
                    </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Teléfono</span>
                    <span class="detail-value">{{ $order->telefono_contacto ?: '—' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Fecha programada</span>
                    <span class="detail-value">
                        {{ $order->fecha_programada ? \Carbon\Carbon::parse($order->fecha_programada)->format('d/m/Y') : '—' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Franja horaria</span>
                    <span class="detail-value">{{ ucfirst($order->franja_horaria ?? 'Flexible') }}</span>
                </div>
            </div>

            {{-- Resumen de Pago ── --}}
            <div class="p-card">
                <div class="p-card-header"><i class="ri-money-dollar-circle-line"></i> Resumen de Pago</div>

                <div class="detail-row">
                    <span class="detail-label">Método de pago</span>
                    <span class="detail-value" style="text-transform:capitalize;">
                        @if ($order->metodo_pago === 'efectivo')
                            💵 Efectivo
                        @elseif($order->metodo_pago === 'yape')
                            🟣 Yape
                        @elseif($order->metodo_pago === 'plin')
                            🔵 Plin
                        @elseif($order->metodo_pago === 'transferencia')
                            🏦 Transferencia
                        @else
                            {{ ucfirst($order->metodo_pago) }}
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Estado de pago</span>
                    <span class="detail-value">
                        @if ($order->estado_pago === 'pagado')
                            <span class="badge bg-success text-white" style="font-size:.72rem;">✓ Pagado</span>
                        @else
                            <span class="badge bg-warning text-dark" style="font-size:.72rem;">Pendiente de pago</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Subtotal</span>
                    <span class="detail-value">S/ {{ number_format($order->subtotal, 2) }}</span>
                </div>

                @if ($order->descuento > 0)
                    <div class="detail-row">
                        <span class="detail-label" style="color:#16a34a;">🎁 Descuento fidelidad</span>
                        <span class="detail-value" style="color:#16a34a;">-S/ {{ number_format($order->descuento, 2) }}</span>
                    </div>
                @endif

                <div class="detail-row" style="font-size:1.05rem; font-weight:700; border-bottom:none; padding-top:.75rem; color:#1e293b;">
                    <span>Total a Pagar</span>
                    <span style="color:#0b5ed7;">S/ {{ number_format($order->total, 2) }}</span>
                </div>
            </div>

        </div>

    </div>

    {{-- Responsive override --}}
    <style>
        @media (max-width: 768px) {
            div[style*="grid-template-columns:1fr 340px"] {
                display: block !important;
            }

            div[style*="grid-template-columns:1fr 340px"] > *:first-child {
                margin-bottom: 1.25rem;
            }
        }
    </style>
@endsection
