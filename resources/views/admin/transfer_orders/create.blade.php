@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="inbox"></i></div>
                        Registro movimiento de almac&eacute;n
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
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm d-flex align-items-center py-2 px-2" role="alert">
                        <i class="ri-alert-line fs-5 text-warning me-2"></i>
                        <div class="flex-grow-1">
                            Se recomienda llevar a cabo esta acci&oacute;n durante horarios sin ventas para asegurar un control m&aacute;s preciso del stock disponible. Adem&aacute;s, antes de agregar productos, aseg&uacute;rate de seleccionar el almac&eacute;n de despacho correspondiente.
                        </div>
                    </div>

                    <form id="form_save_transfer" class="form">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <label for="serie">Serie</label>
                                <input type="text" id="serie" class="form-control text-uppercase" name="serie" required />
                            </div>
    
                            <div class="col-md-3 mb-3">
                                <label for="correlativo">Número</label>
                                <input type="text" id="correlativo" class="form-control text-uppercase" name="correlativo" required />
                            </div>
    
                            <div class="col-md-3 mb-3">
                                <label for="fecha_emision">Fecha de emisión</label>
                                <input type="date" id="fecha_emision" class="form-control" name="fecha_emision" value="{{ date('Y-m-d') }}" required>
                            </div>
    
                            <div class="col-md-3 mb-3">
                                <label for="fecha_vencimiento">Fecha de vencimiento</label>
                                <input type="date" id="fecha_vencimiento" class="form-control" name="fecha_vencimiento" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
    
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="almacen_despacho">Almacén Despacho</label>
                                <select class="form-control" id="almacen_despacho" name="almacen_despacho" required>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
    
                            <div class="col-md-6 mb-3">
                                <label for="almacen_receptor">Almacén Destino</label>
                                <select class="form-control" id="almacen_receptor" name="almacen_receptor" required></select>
                            </div>
                        </div>
    
                        <div class="mb-3">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control text-uppercase" cols="8" rows="3"></textarea>
                        </div>
    
                        <div class="table-responsive mb-4">
                            <div class="table-responsive-sm">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th width="8%" class="text-center">#</th>
                                            <th class="">Descripción</th>
                                            <th class="text-center">Und.</th>
                                            <th class="text-center" width="13%">&nbsp;&nbsp;&nbsp;&nbsp;Cantidad&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                            <th class="text-right" width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody__transfer"></tbody>
                                </table>
                            </div>
                        </div>
    
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <button type="button" class="btn btn-primary btn-add-product">
                                <i class="fas fa-plus mr-2"></i> Agregar Producto
                            </button>
                            <div id="wrapper_totals" class="invoice-calculations"></div>
                        </div>
    
                        <div class="text-end">
                            <a href="{{ route('admin.transfer_orders') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="button" class="btn btn-success btn-save">
                                Guardar cambios
                                <span class="spinner-border spinner-border-sm text-saving d-none" role="status" aria-hidden="true"></span>
                                <span class="ml-2 align-middle text-saving d-none">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.transfer_orders.modals-create')
</div>

@endsection

@section('scripts')
    @include('admin.transfer_orders.js-create')
@endsection