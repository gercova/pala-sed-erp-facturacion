<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de caja</title>
    <style>
        @page {
            margin: 10px 12px;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        .ticket {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .subtitle {
            font-size: 11px;
            margin-bottom: 10px;
        }

        .separator {
            border-top: 1px dashed #9ca3af;
            margin: 10px 0;
        }

        .meta-row,
        .summary-row {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-row td,
        .summary-row td {
            padding: 2px 0;
            vertical-align: top;
        }

        .summary-label {
            width: 62%;
        }

        .summary-value {
            width: 38%;
            text-align: right;
            font-weight: 700;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .total-box {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 8px 10px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="text-center">
            <div class="title">{{ $business?->nombre_comercial ?: 'EasyStock' }}</div>
            @if (!empty($business?->razon_social))
                <div>{{ $business->razon_social }}</div>
            @endif
            @if (!empty($business?->ruc))
                <div>RUC: {{ $business->ruc }}</div>
            @endif
            <div class="subtitle">Resumen de arqueo de caja</div>
        </div>

        <table class="meta-row">
            <tr>
                <td class="muted">Caja</td>
                <td class="text-right">{{ $archingCash->cash?->descripcion ?? '-' }}</td>
            </tr>
            <tr>
                <td class="muted">Responsable</td>
                <td class="text-right">{{ $archingCash->user?->nombres ?? '-' }}</td>
            </tr>
            <tr>
                <td class="muted">Inicio</td>
                <td class="text-right">{{ optional($archingCash->fecha_inicio)->format('d/m/Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="muted">Cierre</td>
                <td class="text-right">{{ optional($archingCash->fecha_fin)->format('d/m/Y') ?? 'Caja abierta' }}</td>
            </tr>
            <tr>
                <td class="muted">Estado</td>
                <td class="text-right">{{ (int) $archingCash->estado === 1 ? 'Abierta' : 'Cerrada' }}</td>
            </tr>
        </table>

        <div class="separator"></div>

        <div class="section-title">Medios de pago</div>
        <table class="summary-row">
            @forelse ($summary['payment_summary'] as $payment)
                <tr>
                    <td class="summary-label">{{ $payment['label'] }}</td>
                    <td class="summary-value">{{ $signo }} {{ number_format((float) $payment['total'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center muted">Sin movimientos registrados.</td>
                </tr>
            @endforelse
        </table>

        <div class="separator"></div>

        <div class="section-title">Resumen financiero</div>
        <table class="summary-row">
            <tr>
                <td class="summary-label">Monto inicial base</td>
                <td class="summary-value">{{ $signo }} {{ number_format((float) $summary['opening_amount'], 2, '.', '') }}</td>
            </tr>
            <tr>
                <td class="summary-label">Ventas vigentes ({{ $summary['sales_count'] }})</td>
                <td class="summary-value">{{ $signo }} {{ number_format((float) $summary['sales_total'], 2, '.', '') }}</td>
            </tr>
            <tr>
                <td class="summary-label">Efectivo cobrado</td>
                <td class="summary-value">{{ $signo }} {{ number_format((float) ($summary['cash_total'] ?? 0), 2, '.', '') }}</td>
            </tr>
            <tr>
                <td class="summary-label">Cobro digital</td>
                <td class="summary-value">{{ $signo }} {{ number_format((float) ($summary['digital_total'] ?? 0), 2, '.', '') }}</td>
            </tr>
            @if ((float) $summary['annulled_total'] > 0)
                <tr>
                    <td class="summary-label">Anulaciones ({{ $summary['annulled_count'] }})</td>
                    <td class="summary-value">{{ $signo }} {{ number_format((float) $summary['annulled_total'], 2, '.', '') }}</td>
                </tr>
            @endif
        </table>

        <div class="separator"></div>

        <div class="section-title">Cuadre de bidones (20L)</div>
        <table class="summary-row">
            <tr>
                <td class="summary-label">Aptos (Devueltos intactos)</td>
                <td class="summary-value">{{ $summary['jugs_intact'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="summary-label">Dañados (Devueltos rotos)</td>
                <td class="summary-value">{{ $summary['jugs_damaged'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="summary-label">Prestados (En clientes)</td>
                <td class="summary-value">{{ $summary['jugs_loaned'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="summary-label">Vendidos (Facturados)</td>
                <td class="summary-value">{{ $summary['jugs_sold'] ?? 0 }}</td>
            </tr>
            @if (!empty($summary['damage_cost']) && (float) $summary['damage_cost'] > 0)
                <tr>
                    <td class="summary-label">Cobro por daños</td>
                    <td class="summary-value">{{ $signo }} {{ number_format((float) $summary['damage_cost'], 2, '.', '') }}</td>
                </tr>
            @endif
        </table>

        @if (($summary['total_orders'] ?? 0) > 0 || ($summary['total_deliveries'] ?? 0) > 0)
            <div class="separator"></div>

            <div class="section-title">Operaciones / Repartos</div>
            <table class="summary-row">
                <tr>
                    <td class="summary-label">Pedidos registrados</td>
                    <td class="summary-value">{{ $summary['total_orders'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-label">Entregas completadas</td>
                    <td class="summary-value">{{ $summary['total_deliveries'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="summary-label">Recaudado en reparto</td>
                    <td class="summary-value">{{ $signo }} {{ number_format((float) ($summary['deliveries_collected'] ?? 0), 2, '.', '') }}</td>
                </tr>
            </table>
        @endif

        <div class="separator"></div>

        <div class="total-box">
            <table class="summary-row">
                <tr>
                    <td class="summary-label"><strong>Efectivo esperado</strong></td>
                    <td class="summary-value"><strong>{{ $signo }} {{ number_format((float) ($summary['expected_cash'] ?? $summary['expected_final']), 2, '.', '') }}</strong></td>
                </tr>
                <tr>
                    <td class="summary-label"><strong>Total cierre</strong></td>
                    <td class="summary-value"><strong>{{ $signo }} {{ number_format((float) $summary['display_final'], 2, '.', '') }}</strong></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
