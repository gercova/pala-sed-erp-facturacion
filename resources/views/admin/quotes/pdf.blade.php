<html>

<head>
    <style>
        * {
            box-sizing: border-box;
            font-size: 11px;
            font-family: sans-serif;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .header .logo {
            width: 18%;
            height: 82px;
            padding-right: 12px;
            vertical-align: middle;
        }

        .header .logo img {
            display: block;
            max-width: 135px;
            max-height: 72px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .header .data {
            width: 57%;
            text-align: center;
            vertical-align: middle;
        }

        .data-name {
            font-weight: bold;
        }

        .data-ruc {
            /* display: inline-block; */
            width: 25%;
            border: 1px solid black;
            border-radius: 5px;
            vertical-align: middle;
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
            border: 1px solid #ccc;
            /* Borde suave */
            border-radius: 5px;
            /* Bordes redondeados */
            background-color: #f9f9f9;
            /* Color de fondo suave */
            text-align: center;
            /* Centrar texto */
        }

        .data-ruc .ruc {
            font-weight: bold;
            /* Negrita para el RUC */
            font-size: 12px;
            /* Tamaño de fuente */
            color: #333;
            /* Color de texto */
            margin-bottom: 8px;
            /* Margen inferior */
        }

        .data-ruc .name {
            background: #e0e7ff;
            /* Color de fondo azul claro */
            padding: 5px;
            /* Espaciado interno */
            margin: 5px 0;
            /* Margen vertical */
            font-size: 12px;
            /* Tamaño de fuente */
            border-radius: 3px;
            /* Bordes redondeados */
            font-weight: bold;
            /* Negrita */
            color: #1e3a8a;
            /* Color del texto */
        }

        .data-ruc .number {
            font-weight: bold;
            /* Negrita para el número */
            font-size: 12px;
            /* Tamaño de fuente */
            color: #333;
            /* Color azul para destacar */
            margin-top: 5px;
            /* Margen superior */
        }

        .price-text {
            padding: 3px;
            border: 1px solid black;
            border-radius: 5px;
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
            border: 0.05px solid #ccc;
            border-radius: 5px;
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
    </style>
</head>

<body>
    @php
        $documentTitle = mb_strtoupper($type_document->descripcion ?? 'COTIZACION');
    @endphp
    <table class="header">
        <tr>
            <td class="logo">
                @php
                    $isLinux = PHP_OS_FAMILY === 'Linux';
                @endphp
                @if (empty($logo))
                    @if ($isLinux)
                        <img src="{{ asset('files/empty_logo.png') }}" alt="EasyStock">
                    @else
                        <img src="{{ public_path('files/empty_logo.png') }}" alt="EasyStock">
                    @endif
                    
                @else
                    @if ($isLinux)
                        <img src="{{ asset('files/logos/' . $logo) }}" alt="EasyStock">
                    @else
                        <img src="{{ public_path('files/logos/' . $logo) }}" alt="EasyStock">
                    @endif
                @endif
            </td>
            <td class="data">
                <div class="data-name">
                    {{ $business->razon_social }}
                </div>
                <div class="data-direction">
                    @php
                        $direccionPrincipal = $business->direccion_principal ?? $business->direccion ?? null;
                        $direccionSucursal = $business->direccion_sucursal ?? null;
                    @endphp
                    @if (filled($direccionPrincipal))
                        <b>Principal:</b> {{ $direccionPrincipal }} <br>
                    @endif
                    @if (filled($direccionSucursal) && $direccionSucursal !== $direccionPrincipal)
                        <b>Sucursal:</b> {{ $direccionSucursal }} <br>
                    @endif
                    <b>Tel&eacute;fono: </b> {{ $business->telefono == null ? '-' : $business->telefono }}
                </div>
            </td>
            <td class="data-ruc">
                <div class="name">{{ $documentTitle }}</div>
                <div class="number">{{ $quote->serie . '-' . $quote->correlativo }}</div>
            </td>
        </tr>
    </table>

    <table id="encabezado" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="font-weight: bold; width: 30%;">RAZ&Oacute;N SOCIAL</td>
            <td style="width: 70%;">: {{ $client->nombres }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">N° DOCUMENTO</td>
            <td>: {{ $client->nro_documento }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">DIRECCION</td>
            <td>: {{ $client->direccion }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">MONEDA</td>
            <td>: {{ $moneda }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">VENDEDOR</td>
            <td>: ADMINISTRADOR</td>
        </tr>
    </table>

    <table class="description"
        style="width: 100%; border-collapse: separate; border-spacing: 0; border-radius: 5px; overflow: hidden; margin-bottom: 20px; border: 0.1px solid #ccc;">
        <thead>
            <tr>
                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 8%;">
                    #</th>
                <th style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc;">
                    DESCRIPCIÓN</th>
                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 10%;">
                    CANT.</th>
                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 10%;">
                    UND.</th>

                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 14%;">
                    P.UNIT.</th>

                <th
                    style="background-color: #f0f0f0; padding: 8px; text-align: center; border: 0.05px solid #ccc; width: 14%;">
                    IMPORTE
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detail as $i => $item)
                <tr>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: center;">{{ $i + 1 }}</td>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: left;">{{ $item['producto'] }}
                    </td>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: center;">
                        {{ intval($item['cantidad']) }}</td>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: center;">{{ $item['unidad'] }}</td>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: center;">
                        {{ number_format($item['precio_unitario'], 2, '.', '') }}</td>
                    <td style="border: 0.05px solid #ccc; padding: 8px; text-align: center;">
                        {{ number_format($item['precio_total'], 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
        </tbody>
    </table>

    <div class="price-text"
        style="border: 1px solid #ccc; border-radius: 5px; padding: 8px; margin-bottom: 15px; display: flex; justify-content: space-between; text-align: center;">
        <span style="font-weight: bold;">SON:</span>
        <span style="font-weight: bold;">{{ $numero_letras }} CON 00/100 {{ $moneda }}</span>
    </div>

    <table class="all" style="table-layout: fixed; width: 100%;">
        <tr>
            <td class="observation" style="width: 60%; overflow: auto; max-height: 100px;">
                <b>OBSERVACIONES:</b>
                <span>{{ $quote->observaciones }}</span>
            </td>
            <td class="all-pay" style="width: 40%; border-left: 1px solid #ccc;">
                <div class="item">
                    <div class="left">OP. GRAVADAS: {{ $signo }}</div>
                    <div class="right">{{ number_format($quote->subtotal, 2, '.', '') }}</div>
                </div>
                <div class="item">
                    <div class="left">IGV: {{ $signo }}</div>
                    <div class="right">{{ $quote->igv }}</div>
                </div>
                <div class="item bold">
                    <div class="left">TOTAL A PAGAR: {{ $signo }}</div>
                    <div class="right">{{ $quote->total }}</div>
                </div>
            </td>
        </tr>
    </table>


    {{-- <div class="bank-accounts" style="margin-top: 10px; width: 60%; margin-left: 0;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <tbody>
                <tr>
                    <td colspan="1" style="text-align: center; font-weight: bold; color: #1e3a8a; padding: 10px; background-color: #f0f0f0; border-bottom: 1px solid #ccc;">
                        Cuentas Bancarias
                    </td>
                </tr>
                <tr style="background-color: #fff;">
                    <td style="border: 0.05px solid #ccc; padding: 10px;">
                        BCP SOLES CUENTA AHORROS: 191245548454844 <br>
                        CCI: 0001-1545484584
                    </td>
                </tr>
                <tr style="background-color: #fff;">
                    <td style="border: 0.05px solid #ccc; padding: 10px;">
                        BCP SOLES CUENTA AHORROS: 191-37421827-0-24 <br>
                        CCI: 001-125158451
                    </td>
                </tr>
            </tbody>
        </table>
    </div> --}}


</body>

</html>
