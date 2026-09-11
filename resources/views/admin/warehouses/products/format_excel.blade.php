<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lista de productos</title>
    <style>
        #cabecera {
            text-align: center;
            text-decoration: underline;
        }

        body {
            font-family: sans-serif;
        }

        h3 {
            margin: 20px 0 10px 0; /* Espaciado controlado */
            padding: 0; /* Sin padding */
        }

        ul {
            list-style-type: none; /* Sin viñetas */
            padding: 0; /* Sin padding */
            margin: 0; /* Sin margen */
        }

        .border-solid {
            border: 1px solid #dee2e6;
        }

        #table_items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        #thead_items {
            width: 100%;
            background-color: rgba(25,175,172,0.5);
        }

        .notes {
            margin-top: 20px;
            font-size: 14px;
            padding: 10px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .notes-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .notes-table th, .notes-table td {
            padding: 5px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
    </style>
</head>

<body>
    <div id="items">
        <table id="table_items">
            <thead id="thead_items" style="font-size: 12px;">
                <tr>
                    <th class="border-solid" style="font-weight: bold; border: border: 1px solid #151515; text-align: left;">descripcion</th>
                    <th class="border-solid" style="font-weight: bold; border: border: 1px solid #151515; text-align: center;">precio_compra</th>
                    <th class="border-solid" style="font-weight: bold; border: border: 1px solid #151515; text-align: center;">precio_venta</th>
                    <th class="border-solid" style="font-weight: bold; border: border: 1px solid #151515; text-align: center;">stock_minimo</th>
                    <th class="border-solid" style="font-weight: bold; border: border: 1px solid #151515; text-align: center;">stock_actual</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productos as $item)
                    <tr>
                        <td class="border-solid">{{ $item->descripcion }}</td>
                        <td class="border-solid" data-format="0.00" style="text-align: center;">{{ number_format($item->precio_compra, 2) }}</td>
                        <td class="border-solid" data-format="0.00" style="text-align: center;">{{ number_format($item->precio_venta, 2) }}</td>
                        <td class="border-solid" style="text-align: center;">{{ ($item->opcion == 1) ? $item->stock_minimo : "" }}</td>
                        <td class="border-solid" style="text-align: center;">{{ ($item->opcion == 1) ? $item->stock_actual : "" }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <br>
    <br>
    <br>
    <div class="notes">
        <h4>Notas:</h4>
        <table class="notes-table">
            <tr>
                <td>Puede modificar todos los campos, excepto la descripción.</td>
            </tr>
            <tr>
                <td>Los registros que no tienen stock mínimo ni stock actual corresponden a servicios. No es necesario realizar cambios a esta información.</td>
            </tr>
        </table>
    </div>
</body>
</html>
