<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; }
        .center { text-align: center; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 0; border-bottom: 1px dashed #bbb; font-size: 10px; }
    </style>
</head>
<body>
    <div class="center mb-2">
        <strong>{{ $business?->razon_social }}</strong><br>
        RUC {{ $business?->ruc }}<br>
        GUIA DE REMISION<br>
        {{ $guide->serie }}-{{ $guide->correlativo }}
    </div>
    <div class="mb-1">Fecha: {{ optional($guide->fecha_emision)->format('Y-m-d') }}</div>
    <div class="mb-1">Cliente: {{ $guide->client?->nombres }}</div>
    <div class="mb-1">Doc: {{ $guide->client?->nro_documento }}</div>
    <div class="mb-1">Modo: {{ (string) $guide->modo_transporte === '01' ? 'Publico' : 'Privado' }}</div>
    <div class="mb-1">Placa principal: {{ $guide->placa_vehiculo ?: '-' }}</div>
    @if ($guide->placa_secundaria)
        <div class="mb-1">Placa carreta: {{ $guide->placa_secundaria }}</div>
    @endif
    <div class="mb-1">Partida: {{ $guide->partida_direccion }}</div>
    <div class="mb-2">Llegada: {{ $guide->llegada_direccion }}</div>

    <table>
        <thead>
            <tr>
                <th align="left">Producto</th>
                <th align="center">Cant.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($guide->items as $item)
                <tr>
                    <td>{{ $item->descripcion }}</td>
                    <td align="center">{{ number_format((float) $item->cantidad, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
