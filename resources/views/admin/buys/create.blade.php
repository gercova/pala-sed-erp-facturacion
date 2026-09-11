@extends('admin.layout')

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="shopping-bag"></i></div>
                        Registro de compra
                    </h1>
                    <div class="small text-muted mt-1">Registro de compras con actualizaci&oacute;n inmediata del costo y stock del almac&eacute;n seleccionado.</div>
                </div>
                <div class="col-12 col-xl-auto mb-3">
                    <a class="btn btn-sm btn-light text-primary" href="{{ route('admin.buys') }}">
                        <i class="me-1" data-feather="arrow-left"></i>
                        Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-body">
                    <form id="form_save_buy" class="form form-vertical">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-3 mb-3">
                                <label for="idtipo_comprobante" class="form-label">Tipo Comprobante</label>
                                <select class="form-control form-select" id="idtipo_comprobante" name="idtipo_comprobante">
                                    @foreach ($type_documents_p as $type_document)
                                        <option value="{{ $type_document->id }}">{{ $type_document->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-2 mb-3">
                                <label for="serie" class="form-label">Serie</label>
                                <input type="text" id="serie" class="form-control text-uppercase reque" name="serie" />
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-12 col-md-3 mb-3">
                                <label for="correlativo" class="form-label">N&uacute;mero</label>
                                <input type="text" id="correlativo" class="form-control text-uppercase reque" name="correlativo" />
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-12 col-md-2 mb-3">
                                <label for="fecha_emision" class="form-label">Fecha de emisi&oacute;n</label>
                                <input type="date" id="fecha_emision" class="form-control" name="fecha_emision" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-12 col-md-2 mb-3">
                                <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento</label>
                                <input type="date" id="fecha_vencimiento" class="form-control" name="fecha_vencimiento" value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label for="dni_ruc" class="form-label d-flex align-items-center gap-2">
                                    <span>Proveedor</span>
                                    <small class="text-primary fw-bold btn-create-provider" style="cursor: pointer">[+ Nuevo]</small>
                                </label>
                                <select class="form-control form-select reque" id="dni_ruc" name="dni_ruc">
                                    <option value=""></option>
                                    @foreach ($providers as $provider)
                                        <option value="{{ $provider->id }}">
                                            {{ $provider->nro_documento . ' - ' . $provider->nombres }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-12 col-md-3 mb-3">
                                <label for="modo_pago" class="form-label">Modo de Pago</label>
                                <select class="form-control form-select reque" id="modo_pago" name="modo_pago">
                                    <option value=""></option>
                                    @foreach ($modo_pagos as $modo_pago)
                                        <option value="{{ $modo_pago->id }}">{{ $modo_pago->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3 mb-3">
                                <label for="tipo_cambio" class="form-label">Tipo de cambio</label>
                                <input type="text" id="tipo_cambio" class="form-control" name="tipo_cambio" value="0.00" readonly>
                            </div>
                        </div>

                        <div class="alert alert-light border py-2 px-3 mt-2 mb-0" role="alert">
                            Selecciona primero el almac&eacute;n y luego el producto para que el costo se aplique al stock correcto.
                        </div>

                        <div class="row invoice-add mt-4">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless align-middle">
                                        <thead class="border-bottom">
                                            <tr>
                                                <th width="5%" class="text-center">#</th>
                                                <th width="30%">Descripci&oacute;n</th>
                                                <th class="text-center">Und.</th>
                                                <th class="text-center" width="12%">Cantidad</th>
                                                <th class="text-center" width="12%">Precio Compra</th>
                                                <th class="text-center" width="12%">Importe</th>
                                                <th width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_buys"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm btn-add-product">
                                    <i data-feather="plus" class="me-1"></i> Agregar Producto
                                </button>
                            </div>

                            <div class="col-md-12 d-flex justify-content-end mt-4">
                                <div id="wrapper_totals" class="invoice-calculations" style="min-width: 200px;"></div>
                            </div>

                            <div class="col-12 mt-4 border-top pt-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.buys') }}" class="btn btn-light">Cancelar</a>
                                    <button type="button" class="btn btn-success btn-save">
                                        <span class="text-save">Guardar Compra</span>
                                        <span class="spinner-border spinner-border-sm d-none text-saving" role="status"></span>
                                        <span class="ms-1 d-none text-saving">Guardando...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.buys.modals-create')
    @include('admin.providers.modal-register', ['typeDocuments' => $typeDocuments])
</div>
@endsection

@section('scripts')
    @include('admin.buys.js-create')
    @include('admin.providers.js-register')
    @include('admin.products.js-register')
@endsection
