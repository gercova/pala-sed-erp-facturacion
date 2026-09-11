@extends('admin.layout')

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="grid"></i></div>
                        Generador de Código QR para Pedidos
                    </h1>
                    <div class="small text-muted mt-1">Genera e imprime etiquetas y stickers QR para pegar en bidones, volantes o magnetos de refrigerador.</div>
                </div>
                <div class="col-auto mb-3">
                    <a href="{{ route('admin.deliveries') }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Volver a Despachos
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-4">
    <div class="row justify-content-center">
        <!-- Panel de Configuración del QR -->
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold text-dark">
                    <i class="ri-settings-4-line me-1 text-primary"></i> Configuración de la Etiqueta QR
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">URL Pública de Pedidos</label>
                        <div class="input-group">
                            <input type="text" id="qr_url" class="form-control" value="{{ $publicUrl }}" readonly>
                            <a href="{{ $publicUrl }}" target="_blank" class="btn btn-outline-primary" title="Abrir en pestaña nueva">
                                <i class="ri-external-link-line"></i>
                            </a>
                        </div>
                        <small class="text-muted">Los clientes que escaneen el QR ingresarán a este portal móvil.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título o Lema del Sticker</label>
                        <input type="text" id="qr_slogan" class="form-control" value="¡Pide tu Recarga de Agua Aquí!">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Texto de Ayuda / Instrucción</label>
                        <input type="text" id="qr_instruction" class="form-control" value="Escanea con tu celular y recibe tu pedido en minutos">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Teléfono / WhatsApp de Contacto</label>
                        <input type="text" id="qr_phone" class="form-control" value="{{ $business->telefono ?? '+51 999 999 999' }}">
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-primary" onclick="window.print();">
                            <i class="ri-printer-line me-1"></i> Imprimir Etiquetas / Stickers
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista Previa de la Etiqueta Imprimible -->
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header fw-bold text-dark">
                    <i class="ri-eye-line me-1 text-primary"></i> Vista Previa de la Etiqueta para Bidón
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                    <!-- Contenedor del Sticker Imprimible -->
                    <div id="printable-sticker" class="border rounded-4 p-4 text-center bg-white shadow-sm" style="max-width: 320px; border-width: 2px !important; border-color: #0061f2 !important;">
                        <h5 class="fw-bold text-primary mb-1">{{ $business->razon_social ?? 'DISTRIBUIDORA DE AGUA' }}</h5>
                        <p class="small fw-semibold text-dark mb-3" id="preview_slogan">¡Pide tu Recarga de Agua Aquí!</p>

                        <!-- Imagen QR Dinámica -->
                        @php
                            $qrImgUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($publicUrl);
                        @endphp
                        <div class="p-2 border rounded-3 bg-light d-inline-block mb-3">
                            <img id="preview_qr_img" src="{{ $qrImgUrl }}" alt="Código QR de Pedidos" style="width: 180px; height: 180px;">
                        </div>

                        <p class="small text-muted mb-2 px-2" id="preview_instruction" style="font-size: 11px;">
                            Escanea con tu celular y recibe tu pedido en minutos
                        </p>

                        <div class="border-top pt-2 mt-2">
                            <span class="small fw-bold text-dark">
                                <i class="ri-whatsapp-line text-success"></i> Pedidos: <span id="preview_phone">{{ $business->telefono ?? '+51 999 999 999' }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-sticker, #printable-sticker * {
            visibility: visible;
        }
        #printable-sticker {
            position: absolute;
            left: 50%;
            top: 20%;
            transform: translateX(-50%);
            box-shadow: none !important;
            border: 2px solid #000 !important;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        $('#qr_slogan').on('input', function() {
            $('#preview_slogan').text($(this).val());
        });

        $('#qr_instruction').on('input', function() {
            $('#preview_instruction').text($(this).val());
        });

        $('#qr_phone').on('input', function() {
            $('#preview_phone').text($(this).val());
        });
    });
</script>
@endsection
