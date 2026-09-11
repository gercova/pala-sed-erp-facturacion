<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $name }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 5px;
            color: #333;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .box {
            border: 1px solid #dcdcdc;
            padding: 8px;
            margin-bottom: 8px;
            margin-top: 10px;
            text-align: right;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }

        .subtitle {
            font-size: 13px;
            font-weight: bold;
        }

        .small {
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #f5f5f5;
            border-bottom: 1px solid #ccc;
            padding: 6px;
            font-size: 11px;
        }

        .table td {
            padding: 5px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        .totales td {
            padding: 6px;
            font-size: 11px;
        }

        .total-final {
            background: #f5f5f5;
            font-size: 13px;
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #999;
            margin: 5px 0;
        }

        img {
            max-width: 150px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body> <!-- CABECERA -->
    <div class="text-center"> @php $isLinux = PHP_OS_FAMILY === 'Linux'; @endphp @if (empty($logo))
            <img src="{{ $isLinux ? asset('files/empty_logo.png') : public_path('files/empty_logo.png') }}">
        @else
            <img src="{{ $isLinux ? asset('files/logos/' . $logo) : public_path('files/logos/' . $logo) }}">
            @endif <div class="title">{{ $business->razon_social }}</div>
            @php
                $direccionPrincipal = $business->direccion_principal ?? $business->direccion ?? null;
                $direccionSucursal = $business->direccion_sucursal ?? null;
            @endphp
            @if (filled($direccionPrincipal))
                <div class="small">Principal: {{ $direccionPrincipal }}</div>
            @endif
            @if (filled($direccionSucursal) && $direccionSucursal !== $direccionPrincipal)
                <div class="small">Sucursal: {{ $direccionSucursal }}</div>
            @endif
            <div class="line"></div>
            <div class="subtitle">{{ $tipo_comprobante->descripcion }}</div>
            <div class="bold">{{ $factura->serie }} - {{ $factura->correlativo }}</div>
    </div> <!-- CLIENTE -->
    <div class="box">
        <table>
            <tr>
                <td class="bold">Cliente:</td>
                <td>{{ $factura->cliente->nombres }}</td>
            </tr>
            <tr>
                <td class="bold">Documento:</td>
                <td>{{ $tipo_documento->descripcion_documento }} {{ $factura->cliente->nro_documento }}</td>
            </tr>
            <tr>
                <td class="bold">Dirección:</td>
                <td>{{ $factura->cliente->direccion }}</td>
            </tr>
            <tr>
                <td class="bold">Fecha:</td>
                <td>{{ date('d/m/Y', strtotime($factura->fecha_emision)) }} {{ $factura->hora }}</td>
            </tr>
            <tr>
                <td class="bold">Vendedor:</td>
                <td>{{ $vendedor }}</td>
            </tr>
        </table>
    </div> <!-- DETALLE -->
    <table class="table">
        <thead>
            <tr>
                <th width="10%">Cant</th>
                <th width="50%">Descripción</th>
                <th width="20%">P/U</th>
                <th width="20%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detalle as $product)
                <tr>
                    <td class="text-center">{{ round($product['cantidad']) }}</td>
                    <td>{{ $product['producto'] }}</td>
                    <td class="text-center">{{ $product['precio_unitario'] }}</td>
                    <td class="text-right">{{ $product['precio_total'] }}</td>
                </tr>
                @endforeach
        </tbody>
    </table> <!-- TOTALES -->
    <table class="totales">
        <tr>
            <td class="text-right bold">Subtotal:</td>
            <td class="text-right">{{ $signo }} {{ $factura->subtotal }}</td>
        </tr>
        <tr>
            <td class="text-right bold">IGV:</td>
            <td class="text-right">{{ $signo }} {{ $factura->igv }}</td>
        </tr>
        <tr class="total-final">
            <td class="text-right">TOTAL:</td>
            <td class="text-right">{{ $signo }} {{ $factura->total }}</td>
        </tr>
    </table>
    <div class="line"></div> <!-- MONTO EN LETRAS -->
    <div class="text-center bold"> Son: {{ $numero_letras }} CON 00/100 {{ $moneda }} </div> <!-- PAGOS -->
    @if ($count_payment != 0)
        <div class="box">
            <div class="bold">M&eacute;todos de Pago</div>
            @foreach ($payment_modes as $pay_mode)
                <div>{{ $pay_mode['modo_pago'] }}: {{ $pay_mode['monto'] }}</div>
            @endforeach
        </div>
    @endif
</body>

</html>
