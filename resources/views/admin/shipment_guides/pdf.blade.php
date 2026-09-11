<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        h2, h3, p { margin: 0 0 8px; }
        .header { margin-bottom: 16px; }
        .grid { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .grid td { padding: 6px; border: 1px solid #ddd; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th, table.items td { border: 1px solid #ddd; padding: 6px; }
        table.items th { background: #f4f6f8; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $business?->razon_social }}</h2>
        <p>RUC: {{ $business?->ruc }}</p>
        <h3>Guia de Remision {{ $guide->serie }}-{{ $guide->correlativo }}</h3>
    </div>

    <table class="grid">
        <tr>
            <td><strong>Fecha emision:</strong> {{ optional($guide->fecha_emision)->format('Y-m-d') }}</td>
            <td><strong>Inicio traslado:</strong> {{ optional($guide->fecha_inicio_traslado)->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><strong>Cliente:</strong> {{ $guide->client?->nombres }}</td>
            <td><strong>Documento:</strong> {{ $guide->client?->nro_documento }}</td>
        </tr>
        <tr>
            <td><strong>Partida:</strong> {{ $guide->partida_direccion }}</td>
            <td><strong>Llegada:</strong> {{ $guide->llegada_direccion }}</td>
        </tr>
        <tr>
            <td><strong>Modo transporte:</strong> {{ (string) $guide->modo_transporte === '01' ? 'Publico' : 'Privado' }}</td>
            <td><strong>Peso total:</strong> {{ number_format((float) $guide->peso_total, 3, '.', '') }} {{ $guide->unidad_peso }}</td>
        </tr>
        <tr>
            <td><strong>Placa principal:</strong> {{ $guide->placa_vehiculo ?: '-' }}</td>
            <td><strong>Placa remolque / carreta:</strong> {{ $guide->placa_secundaria ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Conductor:</strong> {{ $guide->conductor_nombre ?: '-' }}</td>
            <td><strong>Transportista:</strong> {{ $guide->transportista_nombre ?: '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Codigo</th>
                <th>Descripcion</th>
                <th>Unidad</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($guide->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->codigo }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td>{{ $item->unidad }}</td>
                    <td>{{ number_format((float) $item->cantidad, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
