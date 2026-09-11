<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catalogo de productos</title>
    <style>
        body { font-family: sans-serif; }
        #table_items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        #thead_items { background-color: rgba(25,175,172,0.5); }
        .border-solid { border: 1px solid #dee2e6; }
        .notes { margin-top: 20px; font-size: 14px; padding: 10px; border: 1px solid #dee2e6; background-color: #f8f9fa; }
        .notes-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        .notes-table td { padding: 5px; border: 1px solid #dee2e6; text-align: left; }
    </style>
</head>
<body>
    @php
        $excelText = static function ($value) {
            $value = trim((string) $value);

            return $value !== '' ? '="'.$value.'"' : '';
        };
    @endphp

    <table id="table_items">
        <thead id="thead_items" style="font-size: 12px;">
            <tr>
                <th class="border-solid" style="text-align:center;">product_id</th>
                <th class="border-solid" style="text-align:left;">descripcion</th>
                <th class="border-solid" style="text-align:center;">codigo_interno</th>
                <th class="border-solid" style="text-align:center;">codigo_barras</th>
                <th class="border-solid" style="text-align:center;">codigo_sunat</th>
                <th class="border-solid" style="text-align:center;">unidad_codigo</th>
                <th class="border-solid" style="text-align:center;">categoria</th>
                <th class="border-solid" style="text-align:center;">afectacion_igv_codigo</th>
                <th class="border-solid" style="text-align:center;">tipo_item</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $item)
                <tr>
                    <td class="border-solid" style="text-align:center;">{{ $item->id }}</td>
                    <td class="border-solid">{{ $item->descripcion }}</td>
                    <td class="border-solid" style="text-align:center; mso-number-format:'\@';">{{ $excelText($item->codigo_interno) }}</td>
                    <td class="border-solid" style="text-align:center; mso-number-format:'\@';">{{ $excelText($item->codigo_barras) }}</td>
                    <td class="border-solid" style="text-align:center; mso-number-format:'\@';">{{ '="00000000"' }}</td>
                    <td class="border-solid" style="text-align:center;">{{ $item->unidad_codigo }}</td>
                    <td class="border-solid" style="text-align:center;">{{ $item->categoria }}</td>
                    <td class="border-solid" style="text-align:center;">{{ $item->afectacion_igv_codigo }}</td>
                    <td class="border-solid" style="text-align:center;">{{ (int) $item->opcion === 2 ? 'SERVICIO' : 'PRODUCTO' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="notes">
        <h4>Notas:</h4>
        <table class="notes-table">
            <tr><td>No modifique <strong>product_id</strong>. Se usa para identificar el producto.</td></tr>
            <tr><td>En <strong>tipo_item</strong> use PRODUCTO o SERVICIO.</td></tr>
            <tr><td>El <strong>codigo_sunat</strong> del catalogo general se mantiene fijo en <strong>00000000</strong>.</td></tr>
            <tr><td>El catalogo general no administra <strong>precios</strong> ni <strong>stock</strong>. Eso se edita por <strong>almacen</strong>.</td></tr>
        </table>
    </div>
</body>
</html>
