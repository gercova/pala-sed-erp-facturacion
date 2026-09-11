@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="clipboard"></i></div>
                        Gesti&oacute;n de Compras
                    </h1>
                    <div class="small text-muted mt-1">Compras registradas al contado con impacto directo sobre stock por almac&eacute;n.</div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.create_buy') }}"
                        class="dt-button create-new btn btn-success waves-effect waves-light mb-2 btn-create" tabindex="0">
                        <span>
                            <i class="ri-add-circle-line align-middle"></i>
                            <span class="d-none d-sm-inline-block"> Registrar compra</span>
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
                                "timeOut": 2000,
                                "closeButton": false,
                                "progressBar": false
                            };
                            toastr.success("{{ Session::get('exito')['msg'] }}");
                        });
                    </script>
                @endif

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-hover table-sm align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" width="18%" class="text-center">Comprobante</th>
                                    <th scope="col" width="11%" class="text-center">Fecha</th>
                                    <th scope="col" class="text-center">Proveedor</th>
                                    <th scope="col" class="text-center" width="10%">Total</th>
                                    <th scope="col" class="text-center" width="10%">Estado</th>
                                    <th scope="col" width="12%" class="text-center">Acciones</th>
                                </tr>

                                <tr>
                                    <th>
                                        <input type="text" id="voucher-filter" class="form-control form-control-sm" placeholder="Buscar comprobante" />
                                    </th>
                                    <th>
                                        <input type="date" id="date-filter" class="form-control form-control-sm text-center" max="{{ date('Y-m-d') }}" />
                                    </th>
                                    <th>
                                        <input type="text" id="reason-filter" class="form-control form-control-sm" placeholder="Buscar proveedor" />
                                        <input type="text" id="document-filter" class="form-control form-control-sm mt-2" placeholder="Buscar documento" />
                                    </th>
                                    <th>
                                        <input type="text" id="total-filter" class="form-control form-control-sm" placeholder="Buscar total" />
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
    @include('admin.buys.modals')
</div>

@endsection

@section('scripts')
    @include('admin.buys.js-datatable')
    @include('admin.buys.js-store')
@endsection
