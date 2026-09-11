<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pide tu Agua Purificada - {{ $business->razon_social ?? 'Distribuidora de Agua' }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bs-primary: #0061f2;
            --bs-primary-dark: #004ecc;
            --surface-bg: #f4f6fa;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--surface-bg);
            color: #1f2937;
            padding-bottom: 3rem;
        }

        .portal-header {
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 1.25rem 0;
            margin-bottom: 1.5rem;
        }

        .order-card {
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid rgba(33, 40, 50, 0.12);
            box-shadow: 0 4px 16px rgba(18, 38, 63, 0.06);
            overflow: hidden;
        }

        .product-item {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1rem;
            background: #ffffff;
            transition: border-color 0.2s;
        }

        .product-item:hover, .product-item.active {
            border-color: var(--bs-primary);
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: var(--bs-primary);
            cursor: pointer;
            transition: all 0.15s;
        }

        .qty-btn:active {
            background: var(--bs-primary);
            color: #ffffff;
        }

        .btn-submit-order {
            background-color: var(--bs-primary);
            border: none;
            border-radius: 0.75rem;
            padding: 0.85rem 1.5rem;
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            transition: background 0.2s;
        }

        .btn-submit-order:hover {
            background-color: var(--bs-primary-dark);
            color: #ffffff;
        }

        .badge-loyalty {
            background-color: #e0f2fe;
            color: #0284c7;
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.35rem 0.65rem;
        }
    </style>
</head>
<body>

    <!-- Cabecera del Portal (Fondo sólido blanco, sin degradados) -->
    <header class="portal-header">
        <div class="container text-center">
            @php
                $logo = !empty($business->logo) ? asset('files/logos/' . $business->logo) : null;
            @endphp
            @if($logo)
                <img src="{{ $logo }}" alt="Logo" class="mb-2" style="max-height: 55px; max-width: 180px; object-fit: contain;">
            @else
                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-2" style="width: 48px; height: 48px;">
                    <i class="ri-drop-fill fs-3"></i>
                </div>
            @endif
            <h4 class="fw-bold mb-0 text-dark">{{ $business->razon_social ?? 'DISTRIBUIDORA DE AGUA' }}</h4>
            <small class="text-muted"><i class="ri-checkbox-circle-fill text-success"></i> Pedidos en línea y entrega directa a domicilio</small>
        </div>
    </header>

    <div class="container" style="max-width: 650px;">

        <!-- Banner de Promoción Activa (Sin degradado) -->
        @if($promotion && $promotion->activo)
            <div class="alert alert-primary border-primary d-flex align-items-center mb-3 py-2 px-3 rounded-3" role="alert">
                <i class="ri-gift-fill fs-3 text-primary me-2"></i>
                <div>
                    <strong class="d-block text-dark">{{ $promotion->nombre }}</strong>
                    <span class="small text-muted">{{ $promotion->descripcion }}</span>
                </div>
            </div>
        @endif

        <form id="form-qr-order">
            @csrf
            
            <!-- Paso 1: Identificación y Contacto -->
            <div class="order-card mb-3 p-4">
                <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
                    <span class="badge bg-primary text-white rounded-circle me-2" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px;">1</span>
                    ¿A dónde llevamos tu pedido?
                </h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Número de Teléfono / WhatsApp <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="ri-phone-fill"></i></span>
                        <input type="tel" id="input_phone" name="telefono" class="form-control" placeholder="Ej. 999 999 999" required>
                        <button type="button" class="btn btn-outline-primary" id="btn-search-client">
                            <i class="ri-search-line"></i> Buscar
                        </button>
                    </div>
                    <small class="text-muted" style="font-size: 11px;">Si ya has comprado con nosotros, cargaremos tu dirección automáticamente.</small>
                </div>

                <!-- Notificación de Reconocimiento y Fidelidad -->
                <div id="client-loyalty-box" class="d-none alert alert-light border-primary p-3 rounded-3 mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <strong class="text-primary" id="loyalty-client-welcome">¡Hola!</strong>
                        <span id="loyalty-badge" class="badge-loyalty small"></span>
                    </div>
                    <p class="small text-muted mb-0" id="loyalty-message"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Nombre Completo o Empresa <span class="text-danger">*</span></label>
                    <input type="text" id="input_name" name="nombres" class="form-control" placeholder="Tu nombre y apellido" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">DNI o RUC <span class="text-muted fw-normal">(Opcional)</span></label>
                    <input type="text" id="input_dni" name="nro_documento" class="form-control" placeholder="Para boleta o factura">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Dirección de Entrega <span class="text-danger">*</span></label>
                    <input type="text" id="input_address" name="direccion" class="form-control" placeholder="Av. / Jr. / Calle y Nro. Interior/Dpto." required>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold">Referencia de Llegada</label>
                    <input type="text" id="input_reference" name="referencia" class="form-control" placeholder="Frente a la tienda / Timbre blanco">
                </div>
            </div>

            <!-- Paso 2: Selección de Productos -->
            <div class="order-card mb-3 p-4">
                <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
                    <span class="badge bg-primary text-white rounded-circle me-2" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px;">2</span>
                    Selecciona tus bidones de agua
                </h5>

                <div class="d-flex flex-column gap-3 mb-3">
                    @foreach($waterProducts as $product)
                        <div class="product-item d-flex align-items-center justify-content-between" data-id="{{ $product->id }}" data-price="{{ $product->precio_venta }}">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">{{ $product->descripcion }}</h6>
                                <span class="badge bg-light text-primary fw-bold fs-6">S/ {{ number_format($product->precio_venta, 2) }}</span>
                                @if(stripos($product->descripcion, 'recarga') !== false)
                                    <small class="d-block text-muted" style="font-size: 11px;">(Requiere entrega de envase vacío)</small>
                                @endif
                            </div>

                            <!-- Contador de Cantidad -->
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="qty-btn btn-minus">-</button>
                                <span class="qty-count fw-bold px-2 fs-5" style="min-width: 30px; text-align: center;">0</span>
                                <button type="button" class="qty-btn btn-plus">+</button>
                                <input type="hidden" class="input-product-qty" value="0">
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Envases vacíos que entregará el cliente -->
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <label class="form-label small fw-bold text-dark mb-0">
                                <i class="ri-recycle-line text-success"></i> Envases vacíos que entregarás:
                            </label>
                            <small class="text-muted d-block" style="font-size: 11px;">Indica cuántos bidones vacíos tienes listos para entregar.</small>
                        </div>
                        <input type="number" name="envases_a_devolver" id="input_empty_return" class="form-control text-center fw-bold" style="width: 70px;" min="0" value="0">
                    </div>
                </div>
            </div>

            <!-- Paso 3: Horario y Pago -->
            <div class="order-card mb-4 p-4">
                <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
                    <span class="badge bg-primary text-white rounded-circle me-2" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px;">3</span>
                    Fecha de entrega y forma de pago
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Fecha de Entrega</label>
                        <input type="date" name="fecha_programada" class="form-control" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label small fw-bold">Horario Preferido</label>
                        <select name="franja_horaria" class="form-select">
                            <option value="flexible">Lo antes posible</option>
                            <option value="manana">Mañana (08:00 - 13:00)</option>
                            <option value="tarde">Tarde (14:00 - 18:00)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Método de Pago</label>
                        <select name="metodo_pago" class="form-select">
                            <option value="contraentrega">Efectivo contraentrega al recibir</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                            <option value="transferencia">Transferencia bancaria</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Comentario adicional</label>
                        <input type="text" name="notas" class="form-control" placeholder="Ej. Dejar en garita, llevar sencillo para 50 soles...">
                    </div>
                </div>

                <!-- Resumen de Totales -->
                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold text-dark" id="display-subtotal">S/ 0.00</span>
                    </div>
                    <div id="row-discount" class="d-flex justify-content-between align-items-center mb-1 d-none text-success">
                        <span>¡Descuento Fidelidad (1 Bidón GRATIS)!:</span>
                        <span class="fw-bold" id="display-discount">-S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="h5 fw-bold text-dark mb-0">Total a Pagar:</span>
                        <span class="h4 fw-bold text-primary mb-0" id="display-total">S/ 0.00</span>
                    </div>
                </div>
            </div>

            <!-- Botón de Envío -->
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-submit-order shadow-sm" id="btn-submit-order">
                    <i class="ri-check-line me-1"></i> Confirmar y Enviar Pedido
                </button>
            </div>
        </form>

    </div>

    <!-- Modal: Confirmación Exitosa -->
    <div class="modal fade" id="modal-order-success" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle p-3" style="width: 70px; height: 70px;">
                        <i class="ri-checkbox-circle-line fs-1"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-dark mb-1">¡Pedido Registrado con Éxito!</h4>
                <p class="text-muted mb-3">Tu número de orden es: <strong class="text-primary fs-5" id="success-order-code"></strong></p>

                <div class="alert alert-light border text-start small mb-3">
                    <p class="mb-1"><i class="ri-map-pin-line text-primary"></i> <strong>Destino:</strong> <span id="success-address"></span></p>
                    <p class="mb-0"><i class="ri-truck-line text-primary"></i> <strong>Estado:</strong> Recibido, en cola para despacho.</p>
                </div>

                <div class="d-grid gap-2">
                    <a id="btn-whatsapp-confirm" href="#" target="_blank" class="btn btn-success fw-bold d-none">
                        <i class="ri-whatsapp-line me-1"></i> Avisar a la distribuidora por WhatsApp
                    </a>
                    <a id="btn-track-order" href="#" class="btn btn-outline-primary">
                        <i class="ri-map-pin-2-line me-1"></i> Ver Estado de mi Pedido en Tiempo Real
                    </a>
                    <button type="button" class="btn btn-light btn-sm mt-2" onclick="location.reload();">
                        Hacer otro pedido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let clientEligibleForFree = false;

        function calculateTotals() {
            let subtotal = 0;
            let totalRefills = 0;

            $('.product-item').each(function() {
                let price = parseFloat($(this).data('price')) || 0;
                let qty = parseInt($(this).find('.qty-count').text()) || 0;
                subtotal += (price * qty);

                let desc = $(this).find('h6').text().toLowerCase();
                if (desc.includes('recarga')) {
                    totalRefills += qty;
                }
            });

            // Ajustar automáticamente envases vacíos a devolver si el usuario no los cambió
            if ($('#input_empty_return').val() == 0 && totalRefills > 0) {
                $('#input_empty_return').val(totalRefills);
            }

            let discount = 0;
            if (clientEligibleForFree && totalRefills > 0) {
                // Descontar el precio de una recarga (S/ 15.00 o similar)
                let refillPrice = parseFloat($('.product-item:first').data('price')) || 15.00;
                discount = refillPrice;
                $('#row-discount').removeClass('d-none');
                $('#display-discount').text('-S/ ' + discount.toFixed(2));
            } else {
                $('#row-discount').addClass('d-none');
            }

            let total = Math.max(0, subtotal - discount);

            $('#display-subtotal').text('S/ ' + subtotal.toFixed(2));
            $('#display-total').text('S/ ' + total.toFixed(2));
        }

        $(document).ready(function() {
            // Incrementar / decrementar productos
            $('.btn-plus').on('click', function() {
                let item = $(this).closest('.product-item');
                let countSpan = item.find('.qty-count');
                let inputQty = item.find('.input-product-qty');
                let current = parseInt(countSpan.text()) || 0;
                current++;
                countSpan.text(current);
                inputQty.val(current);
                item.addClass('active');
                calculateTotals();
            });

            $('.btn-minus').on('click', function() {
                let item = $(this).closest('.product-item');
                let countSpan = item.find('.qty-count');
                let inputQty = item.find('.input-product-qty');
                let current = parseInt(countSpan.text()) || 0;
                if (current > 0) {
                    current--;
                    countSpan.text(current);
                    inputQty.val(current);
                    if (current === 0) {
                        item.removeClass('active');
                    }
                    calculateTotals();
                }
            });

            // Buscar cliente por teléfono o documento
            $('#btn-search-client').on('click', function() {
                let search = $('#input_phone').val().trim();
                if (!search) {
                    Swal.fire('Atención', 'Ingresa tu número de celular.', 'warning');
                    return;
                }

                $.ajax({
                    url: "{{ route('public.order.check_client') }}",
                    method: "POST",
                    data: { _token: "{{ csrf_token() }}", search: search },
                    success: function(r) {
                        if (r.found) {
                            let c = r.cliente;
                            $('#input_name').val(c.nombres);
                            if (c.nro_documento && !c.nro_documento.startsWith('GEN-')) {
                                $('#input_dni').val(c.nro_documento);
                            }
                            $('#input_address').val(c.direccion);
                            $('#input_reference').val(c.referencia || '');

                            // Fidelidad
                            if (r.loyalty && r.loyalty.has_promotion) {
                                let l = r.loyalty;
                                $('#client-loyalty-box').removeClass('d-none');
                                $('#loyalty-client-welcome').text(`¡Hola de nuevo, ${c.nombres}!`);
                                $('#loyalty-badge').text(`${l.accumulated}/${l.target} compras`);
                                $('#loyalty-message').text(l.message);

                                clientEligibleForFree = l.reward_eligible;
                                calculateTotals();
                            }
                        } else {
                            $('#client-loyalty-box').addClass('d-none');
                            clientEligibleForFree = false;
                            calculateTotals();
                        }
                    }
                });
            });

            // Enviar pedido
            $('#form-qr-order').on('submit', function(e) {
                e.preventDefault();

                let items = [];
                $('.product-item').each(function() {
                    let id = $(this).data('id');
                    let qty = parseInt($(this).find('.qty-count').text()) || 0;
                    if (qty > 0) {
                        items.push({ idproducto: id, cantidad: qty });
                    }
                });

                if (items.length === 0) {
                    Swal.fire('Pedido vacío', 'Por favor selecciona al menos un bidón o producto.', 'warning');
                    return;
                }

                let formData = $(this).serializeArray();
                items.forEach((item, index) => {
                    formData.push({ name: `items[${index}][idproducto]`, value: item.idproducto });
                    formData.push({ name: `items[${index}][cantidad]`, value: item.cantidad });
                });

                $('#btn-submit-order').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Registrando pedido...');

                $.ajax({
                    url: "{{ route('public.order.store') }}",
                    method: "POST",
                    data: $.param(formData),
                    success: function(r) {
                        $('#btn-submit-order').prop('disabled', false).html('<i class="ri-check-line me-1"></i> Confirmar y Enviar Pedido');

                        $('#success-order-code').text(r.order_code);
                        $('#success-address').text($('#input_address').val());
                        $('#btn-track-order').attr('href', r.tracking_url);

                        if (r.whatsapp_url) {
                            $('#btn-whatsapp-confirm').attr('href', r.whatsapp_url).removeClass('d-none');
                        }

                        $('#modal-order-success').modal('show');
                    },
                    error: function(xhr) {
                        $('#btn-submit-order').prop('disabled', false).html('<i class="ri-check-line me-1"></i> Confirmar y Enviar Pedido');
                        Swal.fire('Error', xhr.responseJSON?.msg || 'No se pudo registrar el pedido. Intenta nuevamente.', 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>
