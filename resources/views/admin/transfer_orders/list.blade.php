@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="inbox"></i></div>
                        Gesti&oacute;n de &Oacute;rdenes de traslado
                    </h1>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.create_transfer_order') }}"
                        class="dt-button create-new btn btn-success waves-effect waves-light mb-2 btn-create" tabindex="0">
                        <span>
                            <i class="ri-add-circle-line align-middle"></i>
                            <span class="d-none d-sm-inline-block"> Registrar movimiento</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card custom-card pro-card">
                @if (Session::has('exito'))
                <script>
                    $(document).ready(function() {
                        toastr.options = {
                            "positionClass": "toast-top-right",
                            "timeOut": 2000, // Duración en milisegundos
                            "closeButton": false,
                            "progressBar": false
                        };
                        
                        toastr.success("{{ Session::get('exito')['msg'] }}");
                    });
                </script>
            @endif

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th scope="col" width="15%" class="text-center">Documento</th>
                                    <th scope="col" width="11%" class="text-center">Fecha</th>
                                    <th scope="col" class="text-center">Almac&eacute;n despacho</th>
                                    <th scope="col" class="text-center">Almac&eacute;n receptor</th>
                                    <th scope="col" class="text-center" width="10%">Estado</th>
                                    <th scope="col" width="12%" class="text-center">Acciones</th>
                                </tr>

                                <tr>
                                    <th>
                                        <input type="text" id="document-filter" class="form-control form-control-sm" placeholder="Buscar documento" />
                                    </th>
                                    <th>
                                        <input type="date" id="date-filter" class="form-control form-control-sm text-center" placeholder="Buscar fecha" max="{{ date('Y-m-d') }}" />
                                    </th>
                                    <th>
                                        <input type="text" id="dispatch-filter" class="form-control form-control-sm" placeholder="Buscar almacén despacho" />
                                    </th>
                                    <th>
                                        <input type="text" id="receipt-filter" class="form-control form-control-sm" placeholder="Buscar almacén receptor" />
                                    </th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.transfer_orders.modals')
</div>

@endsection

@section('scripts')
    @include('admin.transfer_orders.js-datatable')
    @include('admin.transfer_orders.js-store')
@endsection