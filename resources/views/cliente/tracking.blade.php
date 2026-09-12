@extends('cliente.layout')
@section('title', 'Seguimiento — ' . $order->codigo_orden)
@section('meta_description', 'Seguimiento de tu pedido ' . $order->codigo_orden)
@section('extra-styles')
    <link rel="stylesheet" href="{{ asset('css/tracking-client.css') }}">
@endsection
@section('content')
    @php
        $estados = [
            'pendiente' => 0,
            'en_camino' => 1,
            'entregado' => 2,
            'cancelado' => -1,
        ];
        $estadoActual = $estados[$order->estado] ?? 0;
        $cancelado = $order->estado === 'cancelado';

        $steps = [
            [
                'key' => 'pendiente',
                'label' => 'Pedido recibido',
                'icon' => '✓',
                'desc' => 'Tu pedido fue registrado en el sistema.',
            ],
            [
                'key' => 'en_camino',
                'label' => 'En camino',
                'icon' => '🚚',
                'desc' => 'Tu repartidor está en ruta hacia tu ubicación.',
            ],
            [
                'key' => 'entregado',
                'label' => 'Entregado',
                'icon' => '✓',
                'desc' => 'Tu pedido fue entregado correctamente.',
            ],
        ];
    @endphp

    <div class="page-header"
        style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:.75rem;">
        <div>
            <h1>Seguimiento del Pedido</h1>
            <p>
                <span
                    style="font-family:monospace; font-weight:700; color:#1e40af; font-size:1rem;">{{ $order->codigo_orden }}</span>
            </p>
        </div>
        <a href="{{ route('cliente.dashboard') }}"
            style="text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1rem; border:1.5px solid #e3e7ef; border-radius:10px; font-size:.85rem; font-weight:600; color:#475569; background:#fff;">
            <i class="ri-arrow-left-line"></i> Mis pedidos
        </a>
    </div>

    @if ($cancelado)
        <div class="p-alert" style="background:#fff5f5; border:1px solid #fecaca; color:#dc2626; margin-bottom:1.5rem;">
            <i class="ri-close-circle-line" style="font-size:1.1rem; flex-shrink:0;"></i>
            <span>Este pedido fue <strong>cancelado</strong>. Si tienes dudas, comunícate con nosotros.</span>
        </div>
    @endif

    <div style="display:grid; grid-template-columns:1fr 340px; gap:1.5rem; align-items:start;">

        {{-- ── Timeline ── --}}
        <div class="p-card">
            <div class="p-card-header"><i class="ri-route-line"></i> Estado del pedido</div>

            @if (!$cancelado)
                <div class="timeline">
                    @foreach ($steps as $idx => $step)
                        @php
                            $stepIdx = $idx;
                            $isDone = $estadoActual > $stepIdx;
                            $isActive = $estadoActual === $stepIdx;
                            $cls = $isDone ? 'done' : ($isActive ? 'active' : 'pending');
                        @endphp
                        <div class="tl-item {{ $cls }}">
                            <div class="tl-dot">
                                @if ($isDone)
                                    <i class="ri-check-line" style="font-size:.8rem;"></i>
                                @elseif($isActive)
                                    <i class="ri-loader-4-line ri-spin" style="font-size:.8rem;"></i>
                                @else<span>{{ $idx + 1 }}</span>
                                @endif
                            </div>
                            <div class="tl-title {{ !$isDone && !$isActive ? 'muted' : '' }}">{{ $step['label'] }}</div>
                            @if ($isDone || $isActive)
                                <div class="tl-time">{{ $step['desc'] }}</div>
                                @if ($isActive && $order->repartidor)
                                    <div
                                        style="margin-top:.5rem; display:inline-flex; align-items:center; gap:.4rem; background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:.35rem .75rem; font-size:.82rem; color:#1d4ed8; font-weight:600;">
                                        <i class="ri-user-line"></i> Repartidor:
                                        {{ $order->repartidor->nombres ?? 'Asignado' }}
                                    </div>
                                @endif
                            @else
                                <div class="tl-time" style="color:#cbd5e1;">Pendiente</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center; padding:2rem; color:#94a3b8;">
                    <i class="ri-close-circle-line"
                        style="font-size:3rem; color:#fca5a5; display:block; margin-bottom:.75rem;"></i>
                    <p style="font-weight:600; color:#dc2626; margin:0;">Pedido cancelado</p>
                </div>
            @endif

            @if ($order->notas)
                <div
                    style="margin-top:1.5rem; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:.85rem 1rem;">
                    <div
                        style="font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#92400e; margin-bottom:.35rem;">
                        <i class="ri-information-line"></i> Notas del pedido
                    </div>
                    <p style="font-size:.875rem; color:#78350f; margin:0;">{{ $order->notas }}</p>
                </div>
            @endif
        </div>

        {{-- ── Resumen lateral ── --}}
        <div style="display:flex; flex-direction:column; gap:1rem;">
            {{-- Datos ── --}}
            <div class="p-card">
                <div class="p-card-header"><i class="ri-file-list-3-line"></i> Detalles</div>
                <div class="detail-row">
                    <span class="detail-label">Estado</span>
                    @php
                        $badgeMap = [
                            'pendiente' => ['badge-pendiente', 'Pendiente'],
                            'en_camino' => ['badge-en_camino', 'En camino'],
                            'entregado' => ['badge-entregado', 'Entregado'],
                            'cancelado' => ['badge-cancelado', 'Cancelado'],
                        ];
                        [$bc, $bl] = $badgeMap[$order->estado] ?? ['badge-pendiente', ucfirst($order->estado)];
                    @endphp
                    <span class="badge-estado {{ $bc }}">{{ $bl }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha programada</span>
                    <span
                        class="detail-value">{{ $order->fecha_programada ? \Carbon\Carbon::parse($order->fecha_programada)->format('d/m/Y') : '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Franja horaria</span>
                    <span class="detail-value">{{ ucfirst($order->franja_horaria ?? 'Flexible') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Método de pago</span>
                    <span class="detail-value" style="text-transform:capitalize;">{{ $order->metodo_pago }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Estado de pago</span>
                    <span class="detail-value">{{ ucfirst($order->estado_pago ?? '—') }}</span>
                </div>
            </div>

            {{-- Entrega ── --}}
            <div class="p-card">
                <div class="p-card-header"><i class="ri-map-pin-2-line"></i> Entrega</div>
                <div class="detail-row">
                    <span class="detail-label">Dirección</span>
                    <span class="detail-value"
                        style="max-width:180px; word-wrap:break-word; text-align:right;">{{ $order->direccion_entrega }}</span>
                </div>
                @if ($order->referencia)
                    <div class="detail-row">
                        <span class="detail-label">Referencia</span>
                        <span class="detail-value"
                            style="max-width:180px; word-wrap:break-word; text-align:right;">{{ $order->referencia }}</span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Teléfono</span>
                    <span class="detail-value">{{ $order->telefono_contacto }}</span>
                </div>
            </div>

            {{-- Totales ── --}}
            <div class="p-card">
                <div class="p-card-header"><i class="ri-money-dollar-circle-line"></i> Resumen</div>
                <div class="detail-row">
                    <span class="detail-label">Subtotal</span>
                    <span class="detail-value">S/ {{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if ($order->descuento > 0)
                    <div class="detail-row">
                        <span class="detail-label" style="color:#16a34a;">🎁 Descuento fidelidad</span>
                        <span class="detail-value" style="color:#16a34a;">-S/
                            {{ number_format($order->descuento, 2) }}</span>
                    </div>
                @endif
                <div class="detail-row" style="font-size:1rem; font-weight:700; border-bottom:none; padding-top:.75rem;">
                    <span>Total</span>
                    <span>S/ {{ number_format($order->total, 2) }}</span>
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

            div[style*="grid-template-columns:1fr 340px"]>*:first-child {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection
