@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="inbox"></i></div>
                        Actualizar cotizaci&oacute;n
                    </h1>
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
                    <form id="form_save_quote" class="form form-vertical">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2">
                                <div class="form-group">
                                    <label for="dni_ruc">Cliente</label>
                                    <small class="text-primary fw-bold btn-create-client" style="cursor: pointer">[+
                                        Nuevo]</small>
                                    <input type="hidden" name="idquote" value="{{ $quote->id }}">
                                    <select class="form-select form-select-xl" id="dni_ruc" name="dni_ruc">
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}">
                                                {{ $client->nro_documento . ' - ' . $client->nombres }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
    
                            <div class="col-12 col-md-2 mb-2">
                                <div class="form-group">
                                    <label for="tipo_cambio">Tipo de cambio</label>
                                    <input type="hidden" name="idalmacenuser" value="{{ Auth::user()['idalmacen'] }}">
                                    <input type="text" id="tipo_cambio" class="form-control"
                                        name="tipo_cambio" value="0.00" readonly>
                                </div>
                            </div>
    
                            <div class="col-12 col-md-2 mb-2">
                                <div class="form-group">
                                    <label for="modo_pago">Forma de Pago:</label>
                                    <select class="form-select form-select-xl" id="modo_pago" name="modo_pago">
                                        @foreach ($modo_pagos as $modo_pago)
                                            <option value="{{ $modo_pago->id }}">{{ $modo_pago->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
    
                            <div class="col-12 col-md-2 mb-3">
                                <label for="fecha_emision">Fecha de emisión</label>
                                <input type="date" id="fecha_emision" class="form-control"
                                    name="fecha_emision" value="{{ $quote->fecha_emision }}" max="{{ date('Y-m-d') }}">
                            </div>
    
                            <div class="col-12 mb-3">
                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" class="form-control text-uppercase" cols="8" rows="3">{{ $quote->observaciones }}</textarea>
                                </div>
                            </div>
                        </div>
    
                        <div class="row invoice-add">
                            <div class="col-md-12">
                                <div class="table-responsive-sm">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th class="">Descripción</th>
                                                <th class="text-center">Und.</th>
                                                <th class="text-center" width="13%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cantidad&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                                <th class="text-center" width="14%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;P. Unitario {{ $signo }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                                <th class="text-center" width="10%">&nbsp;&nbsp;&nbsp;Total&nbsp;&nbsp;&nbsp;</th>
                                                <th class="text-right" width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="wrapper-tbody">
                                            @foreach ($detalle as $item)
                                            <tr id="tr__product__{{ $item['idproducto'] }}">
                                                <td class="d-none"><input type="hidden" name="idproducto" value="{{ $item['idproducto'] }}" data-idalmacen="{{ $item["idalmacen"] }}"></td>
                                                <td>{{ $item["producto"] }}</td>
                                                <td class="text-center">{{ $item["unidad"] }}</td>
                                                <td class="text-right">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text btn-down" style="cursor: pointer;" data-id="{{ $item["idproducto"] }}" data-cantidad="{{ $item["cantidad"] }}" data-precio="{{ $item["precio_unitario"] }}" data-idalmacen="{{ $item["idalmacen"] }}"><i class="ri-subtract-line me-sm-1"></i></span>
                                                        <input type="text" data-id="{{ $item["idproducto"] }}" class="quantity-counter text-center form-control" value="{{ intval($item["cantidad"]) }}" name="input-cantidad">
                                                        <span class="input-group-text btn-up" style="cursor: pointer;" data-id="{{ $item["idproducto"] }}" data-cantidad="{{ $item["cantidad"] }}" data-precio="{{ $item["precio_unitario"] }}" data-idalmacen="{{ $item["idalmacen"] }}"><i class="ri-add-line me-sm-1"></i></span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><input type="text" class="form-control form-control-sm text-center" value="{{ number_format($item["precio_unitario"], 2, ".", "") }}" data-cantidad="'{{ $item["cantidad"] }}" data-id="{{ $item["idproducto"] }}" data-igv="{{ $item['igv'] }}" data-impuesto="{{ $item['impuesto'] }}" data-idalmacen="{{ $item["idalmacen"] }}" name="input-precio"></td>
    
                                                <td class="text-center">{{ number_format(($item["precio_unitario"] * $item["cantidad"]), 2, ".", "") }}</td>
                                                <td class="text-center"><span data-id="{{ $item["idproducto"] }}" class="text-danger btn-delete-product" style="cursor: pointer;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x align-middle mr-25"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
    
                            <div class="col-lg-12 col-md-6 d-flex align-items-end mt-2">
                                <div class="form-group">
                                    <button type="button"
                                        class="btn btn-primary btn-add-product waves-effect waves-float waves-light"
                                        data-repeater-create="">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-plus mr-25">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span class="align-middle">Agregar Producto</span>
                                    </button>
                                </div>
                            </div>
    
                            <div class="col-md-12 d-flex justify-content-end -0 p-sm-4">
                                <div id="wrapper_totals" class="invoice-calculations">
                                    <span class="d-none span__exonerada"></span>
                                        <span class="d-none span__gravada"></span>
                                        <span class="d-none span__inafecta"></span>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="width: 130px !important;">OP. Gravadas:</span>
                                            <span class="fw-medium">{{ $signo }}<span class="span__subtotal">{{ number_format(($quote->subtotal), 2, ".", "") }}</span></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span style="width: 130px !important;">IGV:</span>
                                            <span class="fw-medium">{{ $signo }}<span class="span__igv">{{ number_format($quote->igv, 2, ".", "") }}</span></span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span style="width: 130px !important;">Total:</span>
                                            <span class="fw-medium">{{ $signo }}<span class="span__total">{{ number_format($quote->total, 2, ".", "") }}</span></span>
                                         </div>
                                </div>
                            </div>
    
                            <div class="col-12 wrapper-buttons">
                                <div class="col-12 text-end">
                                    <a href="{{ route('admin.quotes') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="button" class="btn btn-primary btn-save-quote">
                                        <span class="text-save-quote">Guardar </span>
                                        <span class="text-saving-quote d-none" role="status"
                                            aria-hidden="true"><i class="fas fa-spinner fa-spin"></i></span>
                                        <span class="ml-25 align-middle text-saving-quote d-none">Guardando...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.quotes.modals-edit')
    @include('admin.products.modal-add-cart')
    @include('admin.clients.modal-register', ['typeDocuments' => $typeDocuments])
</div>

@endsection

@section('scripts')
    @include('admin.quotes.js-edit')
    @include('admin.clients.js-register')
@endsection
