@extends('admin.layout')

@section('styles')
    <style>
        .users-list-card,
        .users-list-card .card-body,
        .users-list-card .table-responsive {
            overflow: visible;
        }

        .users-list-card .dropdown-menu {
            z-index: 1055;
        }
    </style>
@endsection

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="users"></i></div>
                        Gestión de Usuarios
                    </h1>
                </div>
                <div class="col-auto">
                    <button class="dt-button create-new btn btn-success waves-effect waves-light btn-create mb-3" tabindex="0">
                        <i class="ri-add-circle-line align-middle"></i>
                        <span class="d-none d-sm-inline">Agregar usuario</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card custom-card pro-card users-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th width="14%" class="text-center">Caja</th>
                                    <th width="14%" class="text-center">Rol</th>
                                    <th width="28%" class="text-center">Almacenes</th>
                                    <th width="12%" class="text-center">Estado</th>
                                    <th width="12%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.users.modals')
</div>
@endsection

@section('scripts')
    @include('admin.users.js-datatable')
    @include('admin.users.js-store')
@endsection
