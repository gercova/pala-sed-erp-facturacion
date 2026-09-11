<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleccionar Almacén</title>
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pro-ui.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body class="bg-light">
    <div class="container-xl px-4 py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-2">Selecciona el almacén de trabajo</h1>
                    <p class="text-muted mb-0">Tu usuario tiene acceso a varios almacenes. Elige con cuál quieres continuar.</p>
                </div>

                <div class="row g-3">
                    @foreach ($warehouses as $warehouse)
                        <div class="col-md-6">
                            <form action="{{ route('warehouse.selector.store') }}" method="POST" class="h-100">
                                @csrf
                                <input type="hidden" name="warehouse_id" value="{{ $warehouse->id }}">
                                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                                <button type="submit" class="card border-0 shadow-sm h-100 w-100 text-start {{ (int) $selectedWarehouseId === (int) $warehouse->id ? 'ring ring-primary' : '' }}" style="background: #fff;">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div>
                                                <div class="small text-muted">Almacén</div>
                                                <div class="h5 mb-2">{{ $warehouse->descripcion }}</div>
                                                <div class="text-muted">{{ $warehouse->direccion ?: 'Sin dirección registrada' }}</div>
                                            </div>
                                            <span class="badge {{ (int) $selectedWarehouseId === (int) $warehouse->id ? 'bg-primary' : 'bg-light text-dark border' }}">
                                                {{ (int) $selectedWarehouseId === (int) $warehouse->id ? 'Actual' : 'Seleccionar' }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('login.logout') }}" class="btn btn-outline-secondary">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
