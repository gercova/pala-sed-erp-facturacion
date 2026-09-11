<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $name }}</title>
    <style>
        @page { margin: 8px 10px 12px 10px; }

        html, body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111;
        }

        body { line-height: 1.25; }
        table { width: 100%; border-collapse: collapse; }
        .center { text-align: center; }
        .right { text-align: right; }
        .upper { text-transform: uppercase; }
        .bold { font-weight: 700; }
        .tiny { font-size: 8px; }
        .small { font-size: 9px; }
        .ticket { width: 100%; }
        .logo { display: block; margin: 0 auto 5px; max-width: 92px; max-height: 54px; }
        .company-name { font-size: 13px; font-weight: 700; margin: 2px 0; text-transform: uppercase; }
        .line { margin: 0; }
        .divider { border-top: 1px dashed #333; margin: 6px 0; }
        .doc-title { font-size: 12px; font-weight: 700; margin: 0; text-transform: uppercase; }
        .doc-number { font-size: 12px; font-weight: 700; margin: 2px 0 0; }
        .meta td { padding: 1px 0; vertical-align: top; }
        .meta-label { width: 34%; font-weight: 700; }
        .detail th {
            border-top: 1px solid #222;
            border-bottom: 1px solid #222;
            padding: 3px 2px;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .detail td {
            border-bottom: 1px dashed #ddd;
            padding: 3px 2px;
            font-size: 8.8px;
            vertical-align: top;
        }
        .totals td { padding: 2px 0; font-size: 10px; }
        .totals .label { text-align: left; }
        .totals .amount { text-align: right; }
        .grand-total td {
            border-top: 1px solid #222;
            border-bottom: 1px solid #222;
            padding: 4px 0;
            font-size: 11px;
            font-weight: 700;
        }
        .section-title {
            margin: 6px 0 3px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .qr-box { margin-top: 8px; text-align: center; }
        .qr-box img { width: 78px; height: 78px; display: inline-block; }
        .words { margin-top: 6px; font-size: 9px; font-weight: 700; text-align: center; }
    </style>
</head>

<body>
    @php
        $logo = $business->logo ?? null;
        $isLinux = PHP_OS_FAMILY === 'Linux';
        $amountInWords = mb_strtoupper(trim((string) $amount_in_words));
        $payments = collect($payment_modes ?? []);
        $installmentRows = collect($installments ?? []);
        $discountTotal = (float) ($discount_total ?? 0);
    @endphp

    <div class="ticket">
        <div class="center">
            @if (empty($logo))
                <img src="{{ $isLinux ? asset('files/empty_logo.png') : public_path('files/empty_logo.png') }}" class="logo">
            @else
                <img src="{{ $isLinux ? asset('files/logos/' . $logo) : public_path('files/logos/' . $logo) }}" class="logo">
            @endif

            @php
                $direccionPrincipal = $business->direccion_principal ?? $business->direccion ?? null;
                $direccionSucursal = $business->direccion_sucursal ?? null;
            @endphp
            <p class="company-name">{{ $business->razon_social }}</p>
            @if (filled($direccionPrincipal))
                <p class="line small upper">PRINCIPAL: {{ $direccionPrincipal }}</p>
            @endif
            @if (filled($direccionSucursal) && $direccionSucursal !== $direccionPrincipal)
                <p class="line small upper">SUCURSAL: {{ $direccionSucursal }}</p>
            @endif
            @if (!empty($business->ruc))
                <p class="line bold">RUC: {{ $business->ruc }}</p>
            @endif
        </div>

        <div class="divider"></div>

        <p class="doc-title center">{{ $document_label }}</p>
        <p class="doc-number center">{{ $document_number }}</p>

        <div class="divider"></div>

        <table class="meta">
            <tr>
                <td class="meta-label">Fecha:</td>
                <td>{{ $issued_at }}</td>
            </tr>
            <tr>
                <td class="meta-label">{{ $customer_document_label }}:</td>
                <td>{{ $customer_document_value }}</td>
            </tr>
            <tr>
                <td class="meta-label">Cliente:</td>
                <td class="upper">{{ $customer_name }}</td>
            </tr>
            @if (!empty($customer_address) && $customer_address !== '-')
                <tr>
                    <td class="meta-label">Direccion:</td>
                    <td class="upper">{{ $customer_address }}</td>
                </tr>
            @endif
            <tr>
                <td class="meta-label">Vendedor:</td>
                <td class="upper">{{ $seller }}</td>
            </tr>
            <tr>
                <td class="meta-label">Pago:</td>
                <td>{{ $payment_condition_label ?? 'Contado' }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="detail">
            <thead>
                <tr>
                    <th style="width: 12%;">Cant.</th>
                    <th style="width: 44%; text-align: left;">Descripcion</th>
                    <th style="width: 18%;">P.U.</th>
                    <th style="width: 26%;" class="right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $product)
                    <tr>
                        <td class="center">{{ rtrim(rtrim(number_format((float) $product['cantidad'], 2, '.', ''), '0'), '.') }}</td>
                        <td class="upper">{{ $product['producto'] }}</td>
                        <td class="center">{{ number_format((float) $product['precio_unitario'], 2, '.', '') }}</td>
                        <td class="right">{{ number_format((float) $product['precio_total'], 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table class="totals">
            <tr>
                <td class="label">Subtotal</td>
                <td class="amount">{{ $signo }} {{ number_format((float) $subtotal, 2, '.', '') }}</td>
            </tr>
            @if ($discountTotal > 0)
                <tr>
                    <td class="label">Descuento</td>
                    <td class="amount">- {{ $signo }} {{ number_format($discountTotal, 2, '.', '') }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">IGV</td>
                <td class="amount">{{ $signo }} {{ number_format((float) $igv, 2, '.', '') }}</td>
            </tr>
            <tr class="grand-total">
                <td>IMPORTE TOTAL</td>
                <td class="right">{{ $signo }} {{ number_format((float) $total, 2, '.', '') }}</td>
            </tr>
        </table>

        <div class="words">
            SON: {{ $amountInWords }} {{ mb_strtoupper((string) $moneda) }}
        </div>

        @if ($payments->count() > 0)
            <div class="section-title">Detalle de pago</div>
            @foreach ($payments as $payment)
                <p class="line tiny upper">{{ $payment['modo_pago'] ?? $payment['descripcion'] ?? 'Pago' }}: {{ number_format((float) ($payment['monto'] ?? 0), 2, '.', '') }}</p>
            @endforeach
        @endif

        @if (($payment_condition_label ?? '') === 'Credito' && $installmentRows->count() > 0)
            <div class="section-title">Cuotas</div>
            @foreach ($installmentRows as $installment)
                <p class="line tiny upper">
                    Cuota {{ $installment['nro'] ?? $loop->iteration }}:
                    {{ number_format((float) ($installment['monto'] ?? 0), 2, '.', '') }}
                    - Vence {{ $installment['fecha_vencimiento'] ?? '-' }}
                </p>
            @endforeach
        @endif

        @if (!empty($show_qr) && !empty($qr_image_path))
            <div class="qr-box">
                <img src="{{ $qr_image_path }}" alt="QR">
                <p class="center tiny">Representacion impresa del comprobante electronico.</p>
            </div>
        @endif

        <div class="divider"></div>
        <p class="center tiny">Representacion impresa del comprobante.</p>
    </div>
</body>

</html>
