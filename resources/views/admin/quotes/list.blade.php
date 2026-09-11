@extends('admin.layout')

@section('styles')
    <style>
        .quotes-list-card,
        .quotes-list-card .card-body,
        .quotes-list-card .table-responsive {
            overflow: visible;
        }

        .quotes-list-card .dropdown,
        .quotes-list-card td,
        .quotes-list-card th {
            position: relative;
        }

        .quotes-list-card .dropdown-menu {
            z-index: 1055;
        }

        #table_wrapper .form-control,
        #table_wrapper .form-select,
        #table thead .form-control {
            border-radius: 999px !important;
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: none;
        }

        #table_wrapper .form-control:focus,
        #table_wrapper .form-select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 .12rem rgba(59, 130, 246, .12);
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
                        <div class="page-header-icon"><i data-feather="clipboard"></i></div>
                        Gestión de Cotizaciones
                    </h1>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.create_quote') }}"
                        class="dt-button create-new btn btn-success waves-effect waves-light mb-2 btn-create" tabindex="0">
                        <span>
                            <i class="ri-add-circle-line align-middle"></i>
                            <span class="d-none d-sm-inline-block"> Registrar cotización</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card custom-card pro-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Cotizaciones de hoy</div>
                            <div class="h3 mb-0">{{ $kpi_today_count ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,97,242,.10); color: var(--bs-primary);">
                            <i class="fas fa-file-signature"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card pro-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Total cotizado hoy</div>
                            <div class="h3 mb-0">{{ $signo ?? 'S/' }} {{ number_format(($kpi_today_total ?? 0), 2) }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(0,172,105,.10); color: rgba(0,172,105,1);">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card custom-card pro-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Pendientes</div>
                            <div class="h3 mb-0">{{ $kpi_pending ?? 0 }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(232,136,0,.12); color: rgba(232,136,0,1);">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card custom-card pro-card quotes-list-card">
                @if (Session::has('exito'))
                    <script>
                        $(document).ready(function() {
                            toastr.options = {
                                positionClass: "toast-top-right",
                                timeOut: 2000,
                                closeButton: false,
                                progressBar: false
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
                                    <th scope="col" width="15%" class="text-center">Cotización</th>
                                    <th scope="col" width="11%" class="text-center">Fecha</th>
                                    <th scope="col" class="text-center" width="10%">Documento</th>
                                    <th scope="col" class="text-center">Razón social</th>
                                    <th scope="col" class="text-center" width="10%">Total</th>
                                    <th scope="col" width="12%" class="text-center">Acciones</th>
                                </tr>
                                <tr>
                                    <th>
                                        <input type="text" id="voucher-filter" class="form-control form-control-sm" placeholder="Buscar cotización" />
                                    </th>
                                    <th>
                                        <input type="date" id="date-filter" class="form-control form-control-sm text-center" max="{{ date('Y-m-d') }}" />
                                    </th>
                                    <th>
                                        <input type="text" id="document-filter" class="form-control form-control-sm" placeholder="Buscar documento" />
                                    </th>
                                    <th>
                                        <input type="text" id="reason-filter" class="form-control form-control-sm" placeholder="Buscar razón social" />
                                    </th>
                                    <th>
                                        <input type="text" id="total-filter" class="form-control form-control-sm" placeholder="Buscar total" />
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
    @include('admin.quotes.modals')
</div>
@endsection

@section('scripts')
    @include('admin.quotes.js-datatable')
    @include('admin.quotes.js-store')
    @if (session('exito'))
        <script>
            $.ajax({
                url: "{{ route('admin.print_quote') }}",
                method: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    id: "{{ session('exito')['id'] }}"
                },
                beforeSend: function() {
                    block_content('#layout-content');
                },
                success: function(r) {
                    if (!r.status) {
                        close_block('#layout-content');
                        toast_msg(r.msg, r.type);
                        return;
                    }
                    close_block('#layout-content');
                    let pdf = `{{ asset('files/quotes/${r.pdf}') }}`;
                    if (/Mobi|Android/i.test(navigator.userAgent)) {
                        let link = document.createElement('a');
                        link.href = pdf;
                        link.download = r.pdf;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        var iframe = document.createElement('iframe');
                        iframe.style.display = "none";
                        iframe.src = pdf;
                        document.body.appendChild(iframe);
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    }
                },
                dataType: "json"
            });
        </script>
    @endif
@endsection
