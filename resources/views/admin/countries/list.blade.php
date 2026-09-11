@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="globe"></i></div>
                        Gesti&oacute;n de Pa&iacute;ses
                    </h1>
                </div>
                <div class="col-auto">
                    <button class="dt-button create-new btn btn-success waves-effect waves-light btn-create mb-3" tabindex="0">
                        <i class="ri-add-circle-line align-middle"></i>
                        <span class="d-none d-sm-inline">Agregar pa&iacute;s</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card custom-card pro-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">Prefijo</th>
                                    <th scope="col">Pa&iacute;s</th>
                                    <th scope="col">Cod. Tel&eacute;fono</th>
                                    <th scope="col">Signo</th>
                                    <th scope="col">Moneda</th>
                                    <th scope="col" width="12%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.countries.modals')
</div>

@endsection

@section('scripts')
    @include('admin.countries.js-datatable')
    @include('admin.countries.js-store')
@endsection