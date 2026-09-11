<html>

<head>
    <style>
        * {
            box-sizing: border-box;
            font-size: 11px;
            font-family: sans-serif;
        }

        .header {
            margin-bottom: 15px;
            font-size: 14px;
        }

        .header .logo {
            /* display: inline-block; */
            height: 100px;
            width: 18%;
        }

        .header .logo img {
            width: 100%;
            height: auto
        }

        .header .data {
            /* display: inline-block; */
            width: 69%;
            text-align: center
        }

        .data-name {
            font-weight: bold;
        }

        .data-ruc {
            /* display: inline-block; */
            width: 31%;
            border: 1px solid black;
            border-radius: 5px;
        }

        .data-ruc>div {
            padding: 5px;
            text-align: center;
        }

        .data-ruc>div.name {
            background: #aaa
        }

        .user {
            display: inline-block;
            border: 1px solid black;
            border-radius: 10px;
            padding: 5px;
            margin-bottom: 15px;

        }

        .user>* {
            display: inline-block;
            vertical-align: top;
        }

        .user .w-15 {
            width: 20%;
        }

        .user .w-50 {
            width: 39%;
        }

        .user .w-20 {
            width: 19%;
        }

        .dates {
            display: inline-block;
            border: 1px solid black;
            border-radius: 10px;
            padding: 5px;
            margin-bottom: 15px;
            text-align: center;
            width: 98.5%;
        }

        .dates .w-25 {
            display: inline-block;
            width: 23%;
        }

        .dates .w-25>* {
            display: inline-block;
            width: 100%;
        }

        table.description {
            width: 100%;
            border: 1px solid black;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        table.description .row-1 {
            width: 10%
        }

        table.description .row-2 {
            width: 40%
        }

        table.description tr {
            height: 18px
        }

        .price-text {
            padding: 3px;
            border: 1px solid black;
            border-radius: 10px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .all {
            width: 100%;
            margin-bottom: 10px;
        }

        .all .observation {
            /* display: inline-block; */
            width: 60%;
            height: 50px;
            /* background: blue; */
            vertical-align: top;
        }

        .all .all-pay {
            /* display: inline-block; */
            width: 40%;
            border: 1px solid black;
            border-radius: 10px;
            margin-bottom: 15px;
            padding: 5px;
        }

        .all .all-pay .left {
            display: inline-block;
            width: 63%;
            text-align: right
        }

        .all .all-pay .right {
            display: inline-block;
            width: 35%;
            text-align: right
        }

        .all .all-pay .bold {
            font-weight: bold;
        }

        .info-aside .qr {
            margin-top: 40px;
            margin-right: 20px;
            height: 150px;
            width: 150px;
            display: inline-block;
        }

        .info-aside .qr img {
            width: 100%;
        }

        .info-aside .info {
            display: inline-block;
            width: 75%;
            text-align: center;
            vertical-align: top;
        }

        .info-aside .info .method {
            border-radius: 15px;
            border: 1px solid black;
            padding: 5px;
            margin-bottom: 15px;
        }

        .info-aside .info .method .w-30 {
            display: inline-block;
            width: 32%;
        }

        .info-aside .info .method .w-30 b {
            display: block;
        }

        .info-aside .info .secondary {
            border-radius: 15px;
            border: 1px solid black;
            padding: 5px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        table#encabezado {
            border: 1px solid #000;
        }

        #encabezado td {
            border: 1px solid #ccc;
            /* Borde suave entre celdas */
            padding: 5px;
            /* Espaciado interno */
            vertical-align: middle;
            /* Alineación vertical */
        }

        #encabezado td:first-child {
            background-color: #f2f2f2;
            /* Color de fondo para la columna de títulos */
        }

    .data-ruc {
        border: 1px solid #ccc; /* Borde suave */
        border-radius: 5px; /* Bordes redondeados */
        background-color: #f9f9f9; /* Color de fondo suave */
        text-align: center; /* Centrar texto */
    }

.data-ruc .ruc {
    font-weight: bold; /* Negrita para el RUC */
    font-size: 12px; /* Tamaño de fuente */
    color: #333; /* Color de texto */
    margin-bottom: 8px; /* Margen inferior */
}

.data-ruc .name {
    background: #e0e7ff; /* Color de fondo azul claro */
    padding: 5px; /* Espaciado interno */
    margin: 5px 0; /* Margen vertical */
    font-size: 12px; /* Tamaño de fuente */
    border-radius: 3px; /* Bordes redondeados */
    font-weight: bold; /* Negrita */
    color: #1e3a8a; /* Color del texto */
}

.data-ruc .number {
    font-weight: bold; /* Negrita para el número */
    font-size: 12px; /* Tamaño de fuente */
    color: #333; /* Color azul para destacar */
    margin-top: 5px; /* Margen superior */
}

    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td class="logo">
                @php
                    $isLinux = PHP_OS_FAMILY === 'Linux';
                @endphp
                @if (empty($logo))
                    @if ($isLinux)
                        <img src="{{ asset('files/empty_logo.png') }}" alt="EasyStock" widht="100%" height="100%">
                    @else
                        <img src="{{ public_path('files/empty_logo.png') }}" alt="EasyStock" widht="100%" height="100%">
                    @endif
                    
                @else
                    @if ($isLinux)
                        <img src="{{ asset('files/logos/' . $logo) }}" alt="EasyStock" widht="100%" height="100%">
                    @else
                        <img src="{{ public_path('files/logos/' . $logo) }}" alt="EasyStock" widht="100%" height="100%">
                    @endif
                @endif
            </td>
            <td class="data">
                <div class="data-name">
                    {{ $business->razon_social }}
                </div>
                <div class="data-direction">
                    {{ $business->direccion }} <br>
                    <b>Tel&eacute;fono: </b> {{ ($business->telefono == null) ? '-' : $business->telefono }}
                </div>
            </td>
            <td class="data-ruc">
                <div class="name">ORDEN DE TRASLADO</div>
                <div class="number">{{ $transfer->serie }}-{{ $transfer->correlativo }}</div>
            </td>
        </tr>
    </table>

    <table id="encabezado" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="font-weight: bold; width: 30%;">FECHA DE EMISIÓN</td>
            <td style="width: 70%;">: {{ date('d-m-Y', strtotime($transfer->fecha_emision)) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">ALMACEN DESPACHO</td>
            <td>: {{ $transfer->almacen_despacho }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">ALMACEN DESTINO</td>
            <td>: {{ $transfer->almacen_receptor }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">U. SOLICITA</td>
            <td>: {{ mb_strtoupper($transfer->usuario_solicita) }}</td>
        </tr>
    </table>

    <table class="description"
        style="width: 100%; border-collapse: separate; border-spacing: 0; border-radius: 5px; overflow: hidden; margin-bottom: 20px; border: 0.1px solid #ccc;">
        <thead>
            <tr>
                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 10%;">
                    #</th>
                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 50%;">
                    DESCRIPCIÓN</th>
                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 10%;">
                    CANT.</th>
                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 15%;">
                    UND.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detalle as $i => $item)
                <tr>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: center;">{{ $i + 1 }}</td>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: left;">{{ $item['producto'] }}</td>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: center;">{{ $item["cantidad"] }}</td>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: center;">{{ $item["unidad"] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <table class="all" style="width: 100%;">
        <tr>
            <td class="observation" style="border: 1px solid #000; border-radius: 5px; padding: 10px;">
                <b>OBSERVACIONES:</b>
                <span>{{ $transfer->observaciones }}</span>
            </td>
        </tr>
    </table>

</body>

</html>
