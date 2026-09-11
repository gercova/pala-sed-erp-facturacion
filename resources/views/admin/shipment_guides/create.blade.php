@extends('admin.layout')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-sm-flex align-items-center justify-content-between mt-4 mb-4">
            <div>
                <h1 class="h3 mb-1">Nueva guia de remision</h1>
                <p class="text-muted mb-0">Completa los datos del traslado y registra los productos a mover.</p>
            </div>
            <a href="{{ route('admin.shipment_guides') }}" class="btn btn-outline-secondary">Volver</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="shipmentGuideForm">
                    @csrf

                    <div class="alert alert-warning border-0 mb-4" style="background:#fff8e1; color:#7a6413;">
                        <strong>Recuerda que:</strong>
                        Toda la informacion consignada en la guia de remision debe ser valida y fidedigna; caso contrario SUNAT puede rechazar el documento.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Fecha emision</label>
                            <input type="date" class="form-control" name="fecha_emision" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Inicio traslado</label>
                            <input type="date" class="form-control" name="fecha_inicio_traslado" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Motivo</label>
                            <select class="form-select" name="motivo_traslado_codigo" id="motivo_traslado_codigo">
                                @foreach ($transportReasons as $reason)
                                    <option value="{{ $reason['codigo'] }}" data-description="{{ $reason['descripcion'] }}">{{ $reason['codigo'] }} - {{ $reason['descripcion'] }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="motivo_traslado_descripcion" id="motivo_traslado_descripcion" value="{{ $transportReasons[0]['descripcion'] }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Modo transporte</label>
                            <select class="form-select" name="modo_transporte">
                                <option value="02">Privado</option>
                                <option value="01">Publico</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Destinatario</label>
                            <select class="form-select" name="idcliente" id="guide_client_id">
                                <option value="">Seleccione...</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" data-address="{{ $client->direccion }}" data-ubigeo="{{ $client->ubigeo }}">{{ $client->nro_documento }} - {{ $client->nombres }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Peso total</label>
                            <input type="number" class="form-control" name="peso_total" min="0.001" step="0.001" value="1.000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unidad peso</label>
                            <input type="text" class="form-control" name="unidad_peso" value="KGM" maxlength="3">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ubigeo partida</label>
                            <select class="form-select guide-ubigeo-select" name="partida_ubigeo" id="partida_ubigeo">
                                @if ($partidaUbigeoOption)
                                    <option value="{{ $partidaUbigeoOption['id'] }}" selected>{{ $partidaUbigeoOption['text'] }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Direccion partida</label>
                            <input type="text" class="form-control" name="partida_direccion" value="{{ $warehouse?->direccion }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ubigeo llegada</label>
                            <select class="form-select guide-ubigeo-select" name="llegada_ubigeo" id="llegada_ubigeo"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Direccion llegada</label>
                            <input type="text" class="form-control" name="llegada_direccion">
                        </div>

                        <div class="col-md-4 transport-public-field">
                            <label class="form-label">Transportista</label>
                            <input type="text" class="form-control" name="transportista_nombre">
                        </div>
                        <div class="col-md-4 transport-public-field">
                            <label class="form-label">Documento transportista</label>
                            <input type="text" class="form-control" name="transportista_documento">
                        </div>

                        <div class="col-md-4 transport-private-field">
                            <label class="form-label">Placa principal</label>
                            <input type="text" class="form-control text-uppercase" name="placa_vehiculo" placeholder="Camion o tracto">
                        </div>
                        <div class="col-md-4 transport-private-field">
                            <label class="form-label">Placa remolque / carreta</label>
                            <input type="text" class="form-control text-uppercase" name="placa_secundaria" placeholder="Opcional">
                            <small class="text-muted d-block mt-1">Usala cuando el traslado se haga con remolque, carreta o semirremolque.</small>
                        </div>
                        <div class="col-md-4 transport-private-field">
                            <label class="form-label">Conductor</label>
                            <input type="text" class="form-control" name="conductor_nombre">
                        </div>
                        <div class="col-md-4 transport-private-field">
                            <label class="form-label">Documento conductor</label>
                            <input type="text" class="form-control" name="conductor_documento">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Observaciones</label>
                            <input type="text" class="form-control" name="observaciones">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Producto</label>
                            <select class="form-select" id="guide_product_select">
                                <option value="">Seleccione...</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-code="{{ $product->codigo_interno }}" data-description="{{ $product->descripcion }}">{{ ($product->codigo_interno ?: 'SIN-COD') . ' - ' . $product->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="guide_product_qty" min="0.01" step="0.01" value="1.00">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" id="btnAddGuideItem">Agregar</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle" id="guide_items_table">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th width="120">Codigo</th>
                                    <th width="120">Unidad</th>
                                    <th width="120">Cantidad</th>
                                    <th width="70">Accion</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-success px-4" id="btnSaveShipmentGuide">Guardar guia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('admin.shipment_guides.js-create')
@endsection
