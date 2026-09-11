<!DOCTYPE html>
<html id="layout-content" 
    lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="Sistema de Inventarios y control de Stock">
        <meta name="author" content="Devkro">
        <title>EasyStock</title>
        <link href="{{ asset('npm/litepicker/dist/css/litepicker.css') }}" rel="stylesheet">
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
        <link href="{{ asset('css/pro-ui.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

        <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

        <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon-white.ico') }}">
        <script data-search-pseudo-elements="" defer="" src="{{ asset('ajax/libs/font-awesome/6.3.0/js/all.min.js') }}"></script>
        <script src="{{ asset('ajax/libs/feather-icons/4.29.0/feather.min.js') }}"></script>

        <style>
            /* Enterprise polish without breaking SB Admin Pro */
            .topnav.navbar {
                backdrop-filter: saturate(160%) blur(10px);
                -webkit-backdrop-filter: saturate(160%) blur(10px);
            }
            .topnav.navbar.bg-white {
                background: rgba(255, 255, 255, 0.92) !important;
                border-bottom: 1px solid rgba(33, 40, 50, 0.08);
            }
            .navbar-brand {
                font-weight: 800;
                letter-spacing: -0.02em;
            }
            .sidenav-light {
                background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
                border-right: 1px solid rgba(33, 40, 50, 0.08);
            }
            #layoutSidenav_content main {
                padding-bottom: 2rem;
            }
        </style>

        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

        
        @yield('styles')
    </head>
    <body class="nav-fixed {{ request()->routeIs('admin.products_warehouse') ||
                            request()->is('pos/crear') ? 'sidenav-toggled' : '' }}">
        <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white" id="sidenavAccordion">
            <!-- Sidenav Toggle Button-->
            <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle"><i data-feather="menu"></i></button>
            <!-- Navbar Brand-->
            <!-- * * Tip * * You can use text or an image for your navbar brand.-->
            <!-- * * * * * * When using an image, we recommend the SVG format.-->
            <!-- * * * * * * Dimensions: Maximum height: 32px, maximum width: 240px-->
            <a class="navbar-brand pe-3 ps-4 ps-lg-2 d-flex align-items-center gap-2" href="{{ route('admin.home') }}">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                    style="width: 28px; height: 28px; background: rgba(0,97,242,.10); color: var(--bs-primary); border: 1px solid rgba(0,97,242,.15);">
                    <i class="fas fa-box-open" style="font-size: .9rem;"></i>
                </span>
                <span>EasyStock</span>
            </a>
            <!-- Navbar Search Input-->
            <!-- * * Note: * * Visible only on and above the lg breakpoint-->
            <form class="form-inline me-auto d-none d-lg-block me-3">
                <div class="input-group input-group-joined input-group-solid">
                    <input class="form-control pe-0" type="search" placeholder="Buscar" aria-label="Search" readonly>
                    <div class="input-group-text"><i data-feather="search"></i></div>
                </div>
            </form>
            <!-- Navbar Items-->
            <ul class="navbar-nav align-items-center ms-auto">
                <!-- * * Note: * * Visible only below the lg breakpoint-->
                <li class="nav-item dropdown no-caret me-3 d-lg-none">
                    <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="searchDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i data-feather="search"></i></a>
                    <!-- Dropdown - Search-->
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--fade-in-up" aria-labelledby="searchDropdown">
                        <form class="form-inline me-auto w-100">
                            <div class="input-group input-group-joined input-group-solid">
                                <input class="form-control pe-0" type="text" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                                <div class="input-group-text"><i data-feather="search"></i></div>
                            </div>
                        </form>
                    </div>
                </li>
                <!-- Alerts Dropdown-->
                <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
                    <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownAlerts" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i data-feather="bell"></i>
                        @if($productos_agotar > 0)
                            <span class="badge bg-danger">{{ $productos_agotar }}</span> 
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownAlerts">
                        <h6 class="dropdown-header dropdown-notifications-header">
                            <i class="me-2" data-feather="bell"></i>
                            Notificaciones
                        </h6>
                        
                        @if($productos_agotar > 0)
                            <a class="dropdown-item dropdown-notifications-item" href="#!">
                                <div class="dropdown-notifications-item-icon bg-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="dropdown-notifications-item-content">
                                    <div class="dropdown-notifications-item-content-details">Productos por agotar</div>
                                    <div class="dropdown-notifications-item-content-text">{{ $productos_agotar }}</div>
                                </div>
                            </a>
                        @else
                            <div class="dropdown-item text-center text-muted">Todo est&aacute; bien</div>
                        @endif
                    </div>
                </li>
             
                <!-- User Dropdown-->
                @php
                    $authUser = Auth::user();
                    $currentWarehouse = $authUser?->activeWarehouse;
                    $availableWarehousesCount = $authUser ? $authUser->warehouses()->count() : 0;
                    $primaryRole = optional($authUser?->roles?->first())->name ?: 'USUARIO';
                    $canDashboard = $authUser?->can('admin.home');
                    $canArching = $authUser?->can('admin.arching_cashes');
                    $isBillingReportScreen = request()->routeIs('report.billings.*');
                    $isCreditNoteListScreen = request()->routeIs('admin.billing_credit_notes') || request()->routeIs('billings.credit_notes.get');
                    $isDebitNoteListScreen = request()->routeIs('admin.billing_debit_notes') || request()->routeIs('billings.debit_notes.get');
                    $isShipmentGuideScreen = request()->is('shipment-guides') || request()->is('shipment-guides/*');
                    $isBillingScreen = (request()->is('billings') || (request()->is('billings/*') && ! request()->is('billings/reports*') && ! request()->is('billings/credit-notes*') && ! request()->is('billings/debit-notes*')))
                        && ! $isCreditNoteListScreen
                        && ! $isDebitNoteListScreen;
                    $canVentas = $authUser?->canany(['admin.clients', 'admin.quotes', 'admin.pos', 'admin.sale_notes', 'admin.billings', 'admin.shipment_guides']);
                    $canCompras = $authUser?->canany(['admin.providers', 'admin.buys']);
                    $canInventario = $authUser?->canany(['admin.products', 'admin.categories', 'admin.warehouses', 'admin.transfer_orders']);
                    $canReportes = $authUser?->canany([
                        'report.sales.index',
                        'report.sales.by_product.index',
                        'report.payments.index',
                        'report.billings.sales_register',
                        'report.billings.billing_documents',
                        'report.billings.credit_notes'
                    ]);
                    $canConfiguracion = $authUser?->canany(['admin.business', 'admin.paymodes', 'admin.cashes', 'admin.series', 'admin.users', 'admin.roles']);
                @endphp
                <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
                    <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img class="img-fluid" src="{{ asset('assets/img/illustrations/profiles/profile-4.png') }}"></a>
                    <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                        <h6 class="dropdown-header d-flex align-items-center">
                            <img class="dropdown-user-img" src="{{ asset('assets/img/illustrations/profiles/profile-4.png') }}">
                            <div class="dropdown-user-details">
                                <div class="dropdown-user-details-name">{{ $authUser["nombres"] }}</div>
                                <div class="dropdown-user-details-email"><a href="javascript:void(0)" style="text-decoration: none;">{{ $primaryRole }}</a></div>
                                <div class="small text-muted mt-1">{{ $currentWarehouse?->descripcion ?: 'Sin almacén activo' }}</div>
                            </div>
                        </h6>
                        <div class="dropdown-divider"></div>
                        @if($availableWarehousesCount > 1)
                        <a class="dropdown-item" href="{{ route('warehouse.selector.index', ['redirect_to' => url()->current()]) }}">
                            <div class="dropdown-item-icon"><i data-feather="repeat"></i></div>
                            Cambiar almacén
                        </a>
                        @endif
                        <a class="dropdown-item" href="{{ route('login.logout') }}">
                            <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                            Salir
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sidenav shadow-right sidenav-light">
                    <div class="sidenav-menu">
                        <div class="nav accordion" id="accordionSidenav">
                            <!-- Sidenav Menu Heading (Account)-->
                            <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                            {{-- <div class="sidenav-menu-heading d-sm-none">Cuenta</div> --}}
                            <!-- Sidenav Link (Alerts)-->
                            <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                            <!-- Sidenav Link (Messages)-->
                            <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                            {{-- <a class="nav-link d-sm-none" href="#!">
                                <div class="nav-link-icon"><i data-feather="mail"></i></div>
                                Messages
                                <span class="badge bg-success-soft text-success ms-auto">2 New!</span>
                            </a> --}}
                            <!-- Sidenav Menu Heading (Core)-->
                            <div class="sidenav-menu-heading">Menu</div>
                            @if($canDashboard)
                            <a class="nav-link {{ request()->is('home') ? 'active' : '' }}" href="{{ route('admin.home') }}">
                                <div class="nav-link-icon"><i data-feather="activity"></i></div>
                                Principal
                            </a>
                            @endif
                          
                            <!-- Sidenav Heading (Custom)-->
                            <div class="sidenav-menu-heading">Opciones</div>

                            @if($canArching)
                            <a class="nav-link {{ request()->is('archingcash') ? 'active' : '' }}" href="{{ route('admin.arching_cashes') }}">
                                <div class="nav-link-icon"><i data-feather="dollar-sign"></i></div>
                                Arqueo de cajas
                            </a>
                            @endif

                            @can('admin.clients')
                            <a class="nav-link {{ request()->is('clients*') || request()->is('providers*') ? 'active' : '' }}" href="{{ route('admin.clients') }}">
                                <div class="nav-link-icon"><i data-feather="users"></i></div>
                                Entidad
                            </a>
                            @endcan
                         
                            <!-- Sidenav Accordion (Flows)-->
                            @if($canVentas)
                            <a class="nav-link {{ request()->is('quotes') || 
                                                request()->is('quotes/*') ||
                                                request()->is('pos') ||
                                                request()->is('pos/*') ||
                                                $isBillingScreen ||
                                                $isCreditNoteListScreen ||
                                                $isDebitNoteListScreen ||
                                                $isShipmentGuideScreen ||
                                                request()->is('salenotes') ? '' : 'collapsed' }}" 
                            href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseFlows" aria-expanded="false" aria-controls="collapseFlows">
                                <div class="nav-link-icon"><i data-feather="shopping-cart"></i></div>
                                Ventas
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse {{ request()->is('quotes') || 
                                        request()->is('quotes/*') || 
                                        request()->is('pos') ||
                                        request()->is('pos/*') ||
                                        $isBillingScreen ||
                                        $isCreditNoteListScreen ||
                                        $isDebitNoteListScreen ||
                                        $isShipmentGuideScreen ||
                                        request()->is('salenotes') ? 'show' : '' }}" id="collapseFlows" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav">
                                    @can('admin.quotes')
                                    <a class="nav-link {{ request()->is('quotes') || request()->is('quotes/*') ? 'active' : '' }}" href="{{ route('admin.quotes') }}">Cotizaciones</a>
                                    @endcan
                                    @can('admin.pos')
                                    <a class="nav-link {{ request()->is('pos') || request()->is('pos/*') ? 'active' : '' }}" href="{{ route('admin.pos') }}">Punto de venta</a>
                                    @endcan
                                    @can('admin.sale_notes')
                                    <a class="nav-link {{ request()->is('salenotes') ? 'active' : '' }}" href="{{ route('admin.sale_notes') }}">Notas de venta</a>
                                    @endcan
                                    @can('admin.billings')
                                    <a class="nav-link {{ $isBillingScreen ? 'active' : '' }}" href="{{ route('admin.billings') }}">Comprobantes</a>
                                    <a class="nav-link {{ $isCreditNoteListScreen ? 'active' : '' }}" href="{{ route('admin.billing_credit_notes') }}">Notas de credito</a>
                                    <a class="nav-link {{ $isDebitNoteListScreen ? 'active' : '' }}" href="{{ route('admin.billing_debit_notes') }}">Notas de debito</a>
                                    @endcan
                                    @can('admin.shipment_guides')
                                    <a class="nav-link {{ $isShipmentGuideScreen ? 'active' : '' }}" href="{{ route('admin.shipment_guides') }}">Guias de remision</a>
                                    @endcan
                                    {{-- <a class="nav-link" href="#">Cuentas por cobrar</a> --}}
                                </nav>
                            </div>
                            @endif

                            @if($canCompras)
                            <a class="nav-link {{ request()->is('buys*') ? '' : 'collapsed' }}" 
                                href="javascript:void(0);" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseBuys" 
                                aria-expanded="{{ request()->is('buys*') ? 'true' : 'false' }}" 
                                aria-controls="collapseBuys">
                                    <div class="nav-link-icon"><i data-feather="truck"></i></div>
                                    Compras
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->is('buys*') ? 'show' : '' }}" 
                                    id="collapseBuys" 
                                    data-bs-parent="#accordionSidenav">
                                    <nav class="sidenav-menu-nested nav">
                                        @can('admin.buys')
                                        <a class="nav-link {{ request()->is('buys/create') ? 'active' : '' }}" href="{{ route('admin.create_buy') }}">
                                            Registrar compra
                                        </a>
                                        
                                        <a class="nav-link {{ request()->is('buys') ? 'active' : '' }}" href="{{ route('admin.buys') }}">
                                            Lista de compras
                                        </a>
                                        @endcan

                                        {{-- <a class="nav-link" href="#">
                                            &Oacute;rdenes de compra
                                        </a> --}}
                                    </nav>
                                </div>
                            @endif
                            
                            <!-- Sidenav Accordion (Components)-->
                            @if($canInventario)
                            <a class="nav-link {{ request()->is('products') ||
                                                    request()->is('categories') ||
                                                    request()->is('warehouses') ||
                                                    request()->is('warehouses/*') ||
                                                    request()->is('kardex') ||
                                                    request()->is('kardex/*') ||
                                                    request()->is('transferorders') ||
                                                    request()->is('transferorders/*') ? '' : 'collapsed' }}" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseComponents" aria-expanded="false" aria-controls="collapseComponents">
                                <div class="nav-link-icon"><i data-feather="package"></i></div>
                                Inventario
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse {{ request()->is('products') ||
                                                    request()->is('categories') ||
                                                    request()->is('warehouses') ||
                                                    request()->is('warehouses/*') ||
                                                    request()->is('kardex') ||
                                                    request()->is('kardex/*') ||
                                                    request()->is('transferorders') ||
                                                    request()->is('transferorders/*') ? 'show' : '' }}" id="collapseComponents" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav">
                                    @can('admin.products')
                                    <a class="nav-link {{ request()->is('products') ? 'active' : '' }}" href="{{ route('admin.products') }}">Productos</a>
                                    @endcan
                                    @can('admin.categories')
                                    <a class="nav-link {{ request()->is('categories') ? 'active' : '' }}" href="{{ route('admin.categories') }}">Categor&iacute;as</a>
                                    @endcan
                                    @can('admin.warehouses')
                                    <a class="nav-link {{ request()->is('warehouses') || request()->is('warehouses/*') ? 'active' : '' }}" href="{{ route('admin.warehouses') }}">Almacenes</a>
                                    @endcan
                                    @can('admin.transfer_orders')
                                    <a class="nav-link {{ request()->is('transferorders') || request()->is('transferorders/*') ? 'active' : '' }}" href="{{ route('admin.transfer_orders') }}">&Oacute;rden de traslado</a>
                                    @endcan
                                    @can('admin.products')
                                    <a class="nav-link {{ request()->is('kardex') || request()->is('kardex/*') ? 'active' : '' }}" href="{{ route('admin.kardex') }}">Kardex</a>
                                    @endcan
                                </nav>
                            </div>
                            @endif


                            @if($canReportes)
                            <a class="nav-link {{ request()->routeIs('report.sales.index') || 
                                                    request()->routeIs('report.sales.by_product.index') ||
                                                    request()->routeIs('report.payments.index') ||
                                                    request()->routeIs('report.billings.*') ? '' : 'collapsed' }}" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseReports" aria-expanded="false" aria-controls="collapseReports">
                                <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                                Reportes
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse {{ request()->routeIs('report.sales.index') || 
                                                request()->routeIs('report.sales.by_product.index') ||
                                                request()->routeIs('report.payments.index') ||
                                                request()->routeIs('report.billings.*') ? 'show' : '' }} }}" id="collapseReports" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavReports">
                                    <a class="nav-link {{ request()->routeIs('report.billings.*') ? '' : 'collapsed' }}" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseBillingReports" aria-expanded="false" aria-controls="collapseBillingReports">
                                        Reportes contables
                                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse {{ request()->routeIs('report.billings.*') ? 'show' : '' }}" id="collapseBillingReports" data-bs-parent="#accordionSidenavReports">
                                        <nav class="sidenav-menu-nested nav">
                                            @can('report.billings.sales_register')
                                            <a class="nav-link {{ request()->routeIs('report.billings.sales_register') ? 'active' : '' }}" href="{{ route('report.billings.sales_register') }}">Registro de ventas</a>
                                            @endcan
                                            @can('report.billings.billing_documents')
                                            <a class="nav-link {{ request()->routeIs('report.billings.billing_documents') ? 'active' : '' }}" href="{{ route('report.billings.billing_documents') }}">Documentos emitidos</a>
                                            @endcan
                                            @can('report.billings.credit_notes')
                                            <a class="nav-link {{ request()->routeIs('report.billings.credit_notes') ? 'active' : '' }}" href="{{ route('report.billings.credit_notes') }}">Notas de credito</a>
                                            @endcan
                                        </nav>
                                    </div>
                            
                                   
                                    <!-- Reportes de Stock -->
                                    {{-- <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseStockReports" aria-expanded="false" aria-controls="collapseStockReports">
                                        Reportes de Stock
                                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse" id="collapseStockReports" data-bs-parent="#accordionSidenavReports">
                                        <nav class="sidenav-menu-nested nav">
                                            <a class="nav-link" href="#">Stock actual por almacén</a>
                                            <a class="nav-link" href="#">Productos con stock mínimo o agotados</a>
                                            <a class="nav-link" href="#">Movimientos de stock (entradas/salidas)</a>
                                        </nav>
                                    </div> --}}
                            
                                    <!-- Reportes de Transferencias -->
                                    {{-- <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseTransferReports" aria-expanded="false" aria-controls="collapseTransferReports">
                                        Reportes de Transferencias
                                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse" id="collapseTransferReports" data-bs-parent="#accordionSidenavReports">
                                        <nav class="sidenav-menu-nested nav">
                                            <a class="nav-link" href="#">Historial de órdenes de transferencia</a>
                                            <a class="nav-link" href="#">Transferencias entre almacenes</a>
                                        </nav>
                                    </div> --}}
                            
                                    <!-- Reportes de Clientes -->
                                    {{-- <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseClientReports" aria-expanded="false" aria-controls="collapseClientReports">
                                        Reportes de Clientes
                                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse" id="collapseClientReports" data-bs-parent="#accordionSidenavReports">
                                        <nav class="sidenav-menu-nested nav">
                                            <a class="nav-link" href="#">Clientes con más compras</a>
                                            <a class="nav-link" href="#">Historial de compras por cliente</a>
                                        </nav>
                                    </div> --}}
                                </nav>
                            </div>
                            @endif
                            
                            <!-- Sidenav Accordion (Utilities)-->
                            @if($canConfiguracion)
                            <a class="nav-link {{ request()->is('business') || 
                                                request()->is('cashes') || 
                                                request()->is('series') ||
                                                request()->is('countries') ||
                                                request()->is('users') ? '' : 'collapsed' }}" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseUtilities" aria-expanded="false" aria-controls="collapseUtilities">
                                <div class="nav-link-icon"><i data-feather="tool"></i></div>
                                Configuraci&oacute;n
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse {{ request()->is('business') || 
                                                request()->is('cashes') || 
                                                request()->is('pay-modes') || 
                                                request()->is('series') ||
                                                request()->is('countries') ||
                                                request()->is('users') ||
                                                request()->is('roles') ? 'show' : '' }}" id="collapseUtilities" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav">
                                    @can('admin.business')
                                    <a class="nav-link {{ request()->is('business') ? 'active' : '' }}" href="{{ route('admin.business') }}">Empresa</a>
                                    @endcan
                                    @can('admin.paymodes')
                                    <a class="nav-link {{ request()->is('pay-modes') ? 'active' : '' }}" href="{{ route('admin.paymodes') }}">Metodos de Pago</a>
                                    @endcan
                                    @can('admin.cashes')
                                    <a class="nav-link {{ request()->is('cashes') ? 'active' : '' }}" href="{{ route('admin.cashes') }}">Cajas</a>
                                    @endcan
                                    @can('admin.series')
                                    <a class="nav-link {{ request()->is('series') ? 'active' : '' }}" href="{{ route('admin.series') }}">Series</a>
                                    @endcan
                                    @can('admin.users')
                                    <a class="nav-link {{ request()->is('users') ? 'active' : '' }}" href="{{ route('admin.users') }}">Usuarios</a>
                                    @endcan
                                    @can('admin.roles')
                                    <a class="nav-link {{ request()->is('roles') ? 'active' : '' }}" href="{{ route('admin.roles') }}">Roles</a>
                                    @endcan
                                </nav>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- Sidenav Footer-->
                    <div class="sidenav-footer">
                        <div class="sidenav-footer-content">
                            <div class="sidenav-footer-subtitle">Iniciado sesi&oacute;n como:</div>
                            <div class="sidenav-footer-title">{{ Auth::user()["nombres"] }}</div>
                        </div>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    @yield('content')
                    <!-- Main page content-->
                </main>
            </div>
        </div>
        <script data-cfasync="false" src="{{ asset('cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js') }}"></script>
        <script src="{{ asset('npm/bootstrap%405.2.3/dist/js/bootstrap.bundle.min.js') }}"></script>
        <script>
            var urlVentasMensuales   = "{{ route('home.ventas_mensuales') }}"; 
            var urlReporteIngresos   = "{{ route('home.reporte_ingresos') }}";
            var urlMetodosPagoVentas = "{{ route('home.metodo_pagos') }}";
        </script>
        <script src="{{ asset('js/scripts.js') }}"></script>
        <script src="{{ asset('assets/demo/chart-area-demo.js') }}"></script>
        <script src="{{ asset('assets/demo/chart-bar-demo.js') }}"></script>
        <script src="{{ asset('assets/demo/chart-pie-demo.js') }}"></script>
        <script src="{{ asset('npm/litepicker/dist/bundle.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/block-ui@2.70.1/jquery.blockUI.min.js"></script>
        <script src="{{ asset('js/litepicker.js') }}"></script>
        <script src="{{ asset('js/sb-customizer.js') }}"></script>
        <script src="{{ asset('js/onscan.min.js') }}"></script>
        <script src="{{ asset('js/functions.js') }}"></script>
        
        @yield('scripts')
    </body>
</html>
