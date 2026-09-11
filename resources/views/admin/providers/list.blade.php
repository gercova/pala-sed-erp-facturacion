@extends('admin.layout')
@section('content')

<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="truck"></i></div>
                        Gesti&oacute;n de Proveedores
                    </h1>
                    <div class="small text-muted mt-1">Cat&aacute;logo base para compras, documentos de proveedor y validaci&oacute;n tributaria b&aacute;sica.</div>
                </div>
                <div class="col-auto">
                    <button class="dt-button create-new btn btn-success waves-effect waves-light btn-create-provider mb-3" tabindex="0">
                        <i class="ri-add-circle-line align-middle"></i>
                        <span class="d-none d-sm-inline"> Registrar proveedor</span>
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
                        <table id="table" class="table table-hover table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre o Raz&oacute;n Social</th>
                                    <th width="18%" class="text-center">Documento</th>
                                    <th width="12%" class="text-center">Acciones</th>
                                </tr>
                                <tr>
                                    <th>
                                        <input type="text" id="name-filter" class="form-control form-control-sm" placeholder="Buscar proveedor">
                                    </th>
                                    <th>
                                        <input type="text" id="dni-filter" class="form-control form-control-sm text-center" placeholder="Buscar documento">
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.providers.modal-register', ['typeDocuments' => $typeDocuments])
</div>

@endsection

@section('scripts')
    @include('admin.providers.js-datatable')
    @include('admin.providers.js-register')
@endsection
