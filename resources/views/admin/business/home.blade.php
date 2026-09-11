@extends('admin.layout')

@section('content')
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-xl px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="briefcase"></i></div>
                            Configuración de Empresa
                        </h1>
                        <div class="small text-muted mt-1">Gestión de datos comerciales, domicilio fiscal y credenciales de facturación electrónica.</div>
                    </div>
                    <div class="col-12 col-xl-auto mb-3">
                        <ol class="breadcrumb m-0 text-sm">
                            <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Empresa</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @php
        $logoUrl = !empty($empresa->logo) ? asset('files/logos/' . $empresa->logo) : asset('files/empty_logo.png');
    @endphp

    <div class="container-xl px-4 mt-4">
        <!-- Navegación por pestañas estándar del template -->
        <nav class="nav nav-borders mb-4" role="tablist">
            <a class="nav-link active ms-0" data-bs-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">
                <i class="ri-building-4-line me-1 align-middle"></i> Datos de la Empresa
            </a>
            <a class="nav-link" data-bs-toggle="tab" href="#sunat_tab" role="tab" aria-controls="sunat_tab" aria-selected="false">
                <i class="ri-shield-keyhole-line me-1 align-middle"></i> Facturación y SUNAT
            </a>
        </nav>

        <div class="tab-content">
            <!-- Pestaña 1: Datos de la Empresa -->
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <form id="form-info" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <!-- Columna Izquierda: Logo y Perfil -->
                        <div class="col-xl-4 col-lg-5 mb-4">
                            <div class="card h-100">
                                <div class="card-header fw-bold text-dark">
                                    <i class="ri-image-line me-1 align-middle text-primary"></i> Logo de la Empresa
                                </div>
                                <div class="card-body text-center d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="mb-3 d-flex justify-content-center align-items-center">
                                            <div class="border rounded-3 p-2 bg-light d-inline-flex align-items-center justify-content-center"
                                                style="width: 170px; height: 170px;">
                                                <img id="preview-logo" src="{{ $logoUrl }}" alt="Logo de la Empresa"
                                                    class="img-fluid rounded" style="max-height: 150px; max-width: 150px; object-fit: contain;">
                                            </div>
                                        </div>

                                        <div class="small text-muted mb-3">
                                            Formatos permitidos: <strong>JPG, JPEG o PNG</strong>.<br>
                                            Tamaño máximo permitido: <strong>2 MB</strong>.
                                        </div>

                                        <div class="mb-3">
                                            <label for="company_logo" class="btn btn-primary btn-sm mb-0 cursor-pointer">
                                                <i class="ri-upload-2-line me-1 align-middle"></i> Subir nuevo logo
                                            </label>
                                            <input type="file" class="d-none" id="company_logo" name="logo"
                                                accept="image/jpeg,.jpg,.jpeg,image/png,.png">
                                        </div>
                                    </div>

                                    <!-- Ficha Resumen de la Empresa -->
                                    <div class="mt-4 pt-3 border-top text-start">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <img id="header-company-logo" src="{{ $logoUrl }}" alt="Miniatura Logo"
                                                class="rounded border p-1 bg-white" style="width: 42px; height: 42px; object-fit: contain;">
                                            <div class="overflow-hidden">
                                                <h6 class="fw-bold mb-0 text-dark text-truncate" title="{{ $empresa->razon_social }}">
                                                    {{ $empresa->razon_social }}
                                                </h6>
                                                <span class="text-muted small">RUC: {{ $empresa->ruc }}</span>
                                            </div>
                                        </div>
                                        <div class="text-muted small mb-1">
                                            <i class="ri-map-pin-line me-1 text-primary align-middle"></i> {{ $empresa->direccion }}
                                        </div>
                                        @if(!empty($empresa->telefono))
                                            <div class="text-muted small">
                                                <i class="ri-phone-line me-1 text-primary align-middle"></i> {{ $empresa->telefono }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Formulario de Información General -->
                        <div class="col-xl-8 col-lg-7 mb-4">
                            <div class="card">
                                <div class="card-header fw-bold text-dark">
                                    <i class="ri-file-list-3-line me-1 align-middle text-primary"></i> Información General
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">RUC <span class="text-danger">*</span></label>
                                            <input type="text" name="ruc" class="form-control" value="{{ $empresa->ruc }}"
                                                maxlength="11" placeholder="Ej. 20123456789" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Teléfono de Contacto</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted"><i class="ri-phone-line"></i></span>
                                                <input type="text" name="telefono" class="form-control"
                                                    placeholder="Ej. +51 999 999 999" value="{{ $empresa->telefono }}">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold">Razón Social <span class="text-danger">*</span></label>
                                            <input type="text" name="razon_social" class="form-control text-uppercase"
                                                value="{{ $empresa->razon_social }}" required>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold">Nombre Comercial</label>
                                            <input type="text" name="nombre_comercial" class="form-control text-uppercase"
                                                value="{{ $empresa->nombre_comercial }}">
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold">Dirección Fiscal <span class="text-danger">*</span></label>
                                            <input type="text" name="direccion" class="form-control text-uppercase"
                                                value="{{ $empresa->direccion }}" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">País</label>
                                            <select name="pais" class="form-select bg-light" disabled>
                                                <option value="PE">Perú</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Departamento</label>
                                            <select name="departamento" class="form-control select2_department"></select>
                                        </div>

                                        <div id="wrapper_province" class="col-md-4 d-none">
                                            <label class="form-label small fw-bold">Provincia</label>
                                            <select name="provincia" class="form-control select2_province"></select>
                                        </div>

                                        <div id="wrapper_district" class="col-md-4 d-none">
                                            <label class="form-label small fw-bold">Distrito</label>
                                            <select name="distrito" class="form-control select2_district"></select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Urbanización</label>
                                            <input type="text" name="urbanizacion" class="form-control text-uppercase"
                                                value="{{ $empresa->urbanizacion }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Local / Establecimiento</label>
                                            <input type="text" name="local" class="form-control text-uppercase"
                                                value="{{ $empresa->local }}">
                                        </div>

                                        <!-- Panel de Impuestos (Sin degradado) -->
                                        <div class="col-12 mt-4">
                                            <div class="card bg-light border p-3">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                    <div>
                                                        <label class="form-check-label fw-bold text-dark d-block mb-1" for="cobrar_igv">
                                                            Régimen Tributario / IGV:
                                                            <span id="status-igv" class="{{ $empresa->cobrar_igv ? 'text-primary' : 'text-success' }} fw-bold ms-1">
                                                                {{ $empresa->cobrar_igv ? 'Régimen General (18%)' : 'Exonerado (Ley Amazonía)' }}
                                                            </span>
                                                        </label>
                                                        <small class="text-muted d-block">
                                                            Active esta opción únicamente si la empresa está obligada a recaudar IGV en sus comprobantes de pago.
                                                        </small>
                                                    </div>
                                                    <div class="form-check form-switch ps-0 m-0">
                                                        <input class="form-check-input ms-0" type="checkbox" role="switch"
                                                            id="cobrar_igv" name="cobrar_igv"
                                                            style="width: 48px; height: 24px; cursor: pointer;"
                                                            {{ $empresa->cobrar_igv ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4 pt-2 border-top">
                                        <button type="button" class="btn btn-primary px-4 btn-save-info">
                                            <i class="ri-save-line me-1 align-middle"></i> Guardar cambios
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Pestaña 2: Facturación y SUNAT -->
            <div class="tab-pane fade" id="sunat_tab" role="tabpanel" aria-labelledby="sunat-tab">
                <form id="form_info_user" enctype="multipart/form-data">
                    @csrf
                    <!-- Tarjetas de estado superior (estilo métricas del template) -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="card border-start-lg border-start-primary h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="small fw-bold text-muted text-uppercase">Entorno SUNAT</div>
                                        <span id="sunat-environment-badge"
                                            class="badge {{ (string) $empresa->servidor_sunat === '1' ? 'bg-success-soft text-success' : 'bg-warning-soft text-warning' }} fw-semibold">
                                            {{ (string) $empresa->servidor_sunat === '1' ? 'Producción' : 'Beta' }}
                                        </span>
                                    </div>
                                    <div id="sunat-environment-text" class="text-sm text-muted">
                                        {{ (string) $empresa->servidor_sunat === '1' ? 'Listo para entorno productivo.' : 'Modo pruebas activo para integraciones y validaciones.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-start-lg border-start-success h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="small fw-bold text-muted text-uppercase">Certificado Digital</div>
                                        <span id="certificate-status-badge"
                                            class="badge {{ !empty($empresa->certificado) ? 'bg-success-soft text-success' : 'bg-secondary-soft text-secondary' }} fw-semibold">
                                            {{ !empty($empresa->certificado) ? 'Cargado' : 'Pendiente' }}
                                        </span>
                                    </div>
                                    <div id="certificate-status-text" class="text-sm text-muted text-truncate"
                                        title="{{ !empty($empresa->certificado) ? basename($empresa->certificado) : 'Aún no se ha cargado un certificado digital.' }}">
                                        {{ !empty($empresa->certificado) ? basename($empresa->certificado) : 'Aún no se ha cargado un certificado digital.' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Principal de Configuración SUNAT -->
                    <div class="card mb-4">
                        <div class="card-header fw-bold text-dark">
                            <i class="ri-shield-keyhole-line me-1 align-middle text-primary"></i> Credenciales SUNAT y Facturación Electrónica
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Nombre Comercial para Comprobantes</label>
                                    <input type="text" name="nombre_comercial" class="form-control"
                                        value="{{ $empresa->nombre_comercial }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Usuario Secundario (SOL)</label>
                                    <input type="text" name="usuario_sunat" class="form-control"
                                        placeholder="Ej. MODDATOS" value="{{ $empresa->usuario_sunat }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Clave SOL</label>
                                    <input type="text" name="clave_sunat" class="form-control"
                                        placeholder="Clave secundaria SOL" value="{{ $empresa->clave_sunat }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Clave de Certificado (.pfx)</label>
                                    <input type="text" name="clave_certificado" class="form-control"
                                        placeholder="Contraseña del certificado" value="{{ $empresa->clave_certificado }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">GRE Client ID</label>
                                    <input type="text" name="gre_client_id" class="form-control"
                                        placeholder="ID de API para Guías GRE" value="{{ $empresa->gre_client_id }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">GRE Client Secret</label>
                                    <input type="text" name="gre_client_secret" class="form-control"
                                        placeholder="Clave secreta GRE" value="{{ $empresa->gre_client_secret }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Vencimiento del Certificado</label>
                                    <input type="date" name="vencimiento_certificado" class="form-control"
                                        value="{{ optional($empresa->vencimiento_certificado)->format('Y-m-d') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Certificado Actual</label>
                                    <div id="certificate-current-badge"
                                        class="form-control bg-light text-muted d-flex align-items-center text-truncate"
                                        title="{{ !empty($empresa->certificado) ? basename($empresa->certificado) : 'Sin certificado cargado' }}">
                                        {{ !empty($empresa->certificado) ? basename($empresa->certificado) : 'Sin certificado cargado' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold" for="certificado">Cargar Nuevo Certificado Digital (.pfx)</label>
                                    <div class="input-group">
                                        <label class="input-group-text bg-light text-muted" for="certificado">
                                            <i class="ri-file-code-line"></i>
                                        </label>
                                        <input type="file" name="certificado" class="form-control"
                                            id="certificado" accept=".pfx,application/x-pkcs12">
                                    </div>
                                    <small id="certificate-selected-name" class="text-primary d-block mt-1 fw-semibold" style="font-size: 11px;"></small>
                                </div>

                                <div class="col-12 mt-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase d-block mb-2">Entorno de Emisión SUNAT</label>
                                    <div class="btn-group w-100 shadow-none" role="group">
                                        <input type="radio" class="btn-check" name="servidor_sunat" id="beta"
                                            value="3" {{ $empresa->servidor_sunat == 3 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary py-2 d-flex align-items-center justify-content-center gap-2" for="beta">
                                            <i class="ri-test-tube-line"></i>
                                            <span class="small fw-semibold">Modo Beta (Pruebas)</span>
                                        </label>

                                        <input type="radio" class="btn-check" name="servidor_sunat" id="prod"
                                            value="1" {{ $empresa->servidor_sunat == 1 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary py-2 d-flex align-items-center justify-content-center gap-2" for="prod">
                                            <i class="ri-checkbox-circle-line"></i>
                                            <span class="small fw-semibold">Entorno de Producción</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4 pt-2 border-top">
                                <button type="button" class="btn btn-primary px-4 btn-save-user">
                                    <i class="ri-shield-check-line me-1 align-middle"></i>
                                    <span class="text-btn-user">Actualizar Credenciales</span>
                                    <span class="text-save-user d-none">Actualizando...</span>
                                    <span class="spinner-border spinner-border-sm d-none text-saving-user ms-1"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('admin.business.js-home')
@endsection
