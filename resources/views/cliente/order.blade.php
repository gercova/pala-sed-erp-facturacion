@extends('cliente.layout')
@section('title', 'Nuevo Pedido')
@section('meta_description', 'Realiza tu pedido de agua purificada con entrega a domicilio.')
@section('extra-styles')
    <link rel="stylesheet" href="{{ asset('css/order-client.css') }}">
@endsection

@section('content')
    <div class="page-header"
        style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1>Nuevo Pedido</h1>
            <p>Completa los pasos para registrar tu pedido de agua purificada.</p>
        </div>
        @if ($loyalty['reward_eligible'] ?? false)
            <div
                style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:.65rem 1rem; font-size:.82rem; color:#15803d; font-weight:600; display:flex; align-items:center; gap:.4rem;">
                🎁 ¡Tienes un bidón GRATIS disponible!
            </div>
        @endif
    </div>

    {{-- ── Wizard ─── --}}
    <div class="wizard-steps" id="wizard-steps">
        <div class="wizard-step active" id="step-1" onclick="goToSection(1)">
            <div class="step-num">1</div>
            <div class="step-label">Mis datos</div>
        </div>
        <div class="step-sep"></div>
        <div class="wizard-step" id="step-2" onclick="goToSection(2)">
            <div class="step-num">2</div>
            <div class="step-label">Mi pedido</div>
        </div>
        <div class="step-sep"></div>
        <div class="wizard-step" id="step-3" onclick="goToSection(3)">
            <div class="step-num">3</div>
            <div class="step-label">Entrega y pago</div>
        </div>
    </div>

    <div id="order-sections">

        {{-- ══ SECCIÓN 1: DATOS DEL CLIENTE ══ --}}
        <div class="order-section visible" id="section-1">
            <div class="p-card">
                <div class="p-card-header"><i class="ri-user-line"></i> Mis Datos de Contacto</div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <label class="p-label">Nombre / Razón Social</label>
                        <input type="text" class="p-input" id="s1_nombres" value="{{ $client->nombres }}" readonly
                            style="background:#f1f5f9; cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="p-label">Teléfono</label>
                        <input type="text" class="p-input" id="s1_telefono" value="{{ $client->telefono }}">
                    </div>
                    <div>
                        <label class="p-label">Documento</label>
                        <input type="text" class="p-input" value="{{ $client->nro_documento }}" readonly
                            style="background:#f1f5f9; cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="p-label">Ubigeo / Distrito</label>
                        @php
                            $distritos = [
                                '220601' => 'Tarapoto',
                                '220602' => 'Morales',
                                '220603' => 'La Banda de Shilcayo',
                                '220609' => 'Shapaja',
                            ];
                        @endphp
                        <input type="text" class="p-input" value="{{ $distritos[$client->ubigeo] ?? $client->ubigeo }}"
                            readonly style="background:#f1f5f9; cursor:not-allowed;">
                    </div>
                </div>

                <div class="section-nav">
                    <div></div>
                    <button class="btn-next" onclick="goToSection(2)">
                        Siguiente — Mi pedido <i class="ri-arrow-right-line"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ SECCIÓN 2: PRODUCTOS ══ --}}
        <div class="order-section" id="section-2">
            <div class="p-card">
                <div class="p-card-header"><i class="ri-shopping-basket-line"></i> Selecciona tus Productos</div>

                @if ($products->isEmpty())
                    <div style="text-align:center; padding:2rem; color:#94a3b8;">
                        <i class="ri-box-3-line" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        No hay productos disponibles.
                    </div>
                @else
                    <div class="products-grid" id="products-grid">
                        @foreach ($products as $product)
                            <div class="product-card" id="prod-{{ $product->id }}"
                                onclick="toggleProduct({{ $product->id }}, {{ $product->precio_venta }}, '{{ addslashes($product->descripcion) }}')">
                                <div class="selected-badge"><i class="ri-check-line"></i></div>
                                <div class="prod-name">{{ $product->descripcion }}</div>
                                <div class="prod-price">S/ {{ number_format($product->precio_venta, 2) }}</div>
                                <div class="prod-unit">{{ $product->unidad ?? 'Unidad' }}</div>
                                <div class="qty-control" id="qty-ctrl-{{ $product->id }}" style="display:none;"
                                    onclick="event.stopPropagation()">
                                    <button class="qty-btn" onclick="changeQty({{ $product->id }}, -1)">−</button>
                                    <span class="qty-display" id="qty-{{ $product->id }}">1</span>
                                    <button class="qty-btn" onclick="changeQty({{ $product->id }}, 1)">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Resumen ── --}}
                <div class="order-summary" id="order-summary" style="display:none;">
                    <div
                        style="font-weight:700; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; margin-bottom:.5rem;">
                        Resumen del pedido</div>
                    <div id="summary-items"></div>
                    @if ($loyalty['reward_eligible'] ?? false)
                        <div class="summary-row discount">
                            <span>🎁 Premio fidelidad (1 recarga gratis)</span>
                            <span id="summary-discount">-S/ 0.00</span>
                        </div>
                    @endif
                    <div class="summary-row total">
                        <span>Total estimado</span>
                        <span id="summary-total">S/ 0.00</span>
                    </div>
                </div>

                <div class="section-nav">
                    <button class="btn-prev" onclick="goToSection(1)"><i class="ri-arrow-left-line"></i> Anterior</button>
                    <button class="btn-next" id="btn-next-2" onclick="goToSection(3)" disabled>
                        Siguiente — Entrega y pago <i class="ri-arrow-right-line"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ SECCIÓN 3: ENTREGA + PAGO ══ --}}
        <div class="order-section" id="section-3">
            <div class="p-card">
                <div class="p-card-header"><i class="ri-map-pin-2-line"></i> Dirección de Entrega</div>

                <div style="margin-bottom:1rem;">
                    <label class="p-label">Dirección de entrega *</label>
                    <input type="text" class="p-input" id="f_direccion" placeholder="Jr. Lima 123, Tarapoto"
                        value="{{ $client->direccion }}" oninput="syncAddressFromInput()">
                </div>
                <div style="margin-bottom:1rem;">
                    <label class="p-label">Referencia (punto de referencia)</label>
                    <input type="text" class="p-input" id="f_referencia"
                        placeholder="Frente al parque, casa verde..." value="{{ $client->referencia }}">
                </div>

                {{-- Mapa ── --}}
                <div style="margin-bottom:1rem;">
                    <label class="p-label">Ubica tu dirección en el mapa</label>
                    <div id="map-container">
                        @if (!empty($mapsApiKey))
                            <div id="gmap"></div>
                        @else
                            <div class="map-placeholder">
                                <i class="ri-map-2-line"></i>
                                <span style="font-size:.85rem;">Selector de mapa no disponible.<br>Ingresa tu dirección en
                                    el campo de arriba.</span>
                            </div>
                        @endif
                    </div>
                    <input type="hidden" id="f_coordenadas" name="coordenadas">
                    @if (!empty($mapsApiKey))
                        <p style="font-size:.78rem; color:#94a3b8; margin:.4rem 0 0;">
                            <i class="ri-information-line"></i> Haz clic en el mapa para marcar tu ubicación exacta.
                        </p>
                    @endif
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                    <div>
                        <label class="p-label">Fecha de entrega *</label>
                        <input type="date" class="p-input" id="f_fecha" min="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    <div>
                        <label class="p-label">Franja horaria</label>
                        <select class="p-input" id="f_franja">
                            <option value="manana">Mañana (8am – 12pm)</option>
                            <option value="tarde">Tarde (12pm – 5pm)</option>
                            <option value="flexible" selected>Flexible (cualquier hora)</option>
                        </select>
                    </div>
                </div>

                {{-- Método de pago ── --}}
                <div class="p-card-header"><i class="ri-bank-card-line"></i> Método de Pago</div>
                <div class="pay-grid" id="pay-methods">
                    <label class="pay-option selected" onclick="selectPay(this, 'efectivo')">
                        <input type="radio" name="metodo_pago" value="efectivo" checked>
                        <span class="pay-icon">💵</span>
                        <span class="pay-name">Efectivo</span>
                    </label>
                    <label class="pay-option" onclick="selectPay(this, 'yape')">
                        <input type="radio" name="metodo_pago" value="yape">
                        <span class="pay-icon">🟣</span>
                        <span class="pay-name">Yape</span>
                    </label>
                    <label class="pay-option" onclick="selectPay(this, 'plin')">
                        <input type="radio" name="metodo_pago" value="plin">
                        <span class="pay-icon">🔵</span>
                        <span class="pay-name">Plin</span>
                    </label>
                    <label class="pay-option" onclick="selectPay(this, 'transferencia')">
                        <input type="radio" name="metodo_pago" value="transferencia">
                        <span class="pay-icon">🏦</span>
                        <span class="pay-name">Transferencia</span>
                    </label>
                </div>

                {{-- QR Yape ── --}}
                <div class="qr-box" id="qr-yape">
                    @if (!empty($business->yape_qr))
                        <p style="font-weight:700; color:#334155; margin-bottom:.75rem;">Escanea el QR de Yape</p>
                        <img src="{{ asset('files/qr/' . $business->yape_qr) }}" alt="QR Yape" loading="lazy">
                    @else
                        <p style="font-weight:700; color:#334155; margin-bottom:.5rem;">Pago con Yape</p>
                        <div
                            style="width:180px; height:180px; background:#f3e8ff; border-radius:12px; display:flex; align-items:center; justify-content:center; margin:0 auto; border:1px dashed #a855f7;">
                            <span style="font-size:3rem;">🟣</span>
                        </div>
                    @endif
                    <p class="qr-instructions">Escanea el QR con tu app Yape, realiza el pago y muéstrale el comprobante a
                        tu repartidor.</p>
                </div>

                {{-- QR Plin ── --}}
                <div class="qr-box" id="qr-plin">
                    @if (!empty($business->plin_qr))
                        <p style="font-weight:700; color:#334155; margin-bottom:.75rem;">Escanea el QR de Plin</p>
                        <img src="{{ asset('files/qr/' . $business->plin_qr) }}" alt="QR Plin" loading="lazy">
                    @else
                        <p style="font-weight:700; color:#334155; margin-bottom:.5rem;">Pago con Plin</p>
                        <div
                            style="width:180px; height:180px; background:#e0f2fe; border-radius:12px; display:flex; align-items:center; justify-content:center; margin:0 auto; border:1px dashed #38bdf8;">
                            <span style="font-size:3rem;">🔵</span>
                        </div>
                    @endif
                    <p class="qr-instructions">Escanea el QR con tu app Plin, realiza el pago y muéstrale el comprobante a
                        tu repartidor.</p>
                </div>

                {{-- Notas ── --}}
                <div style="margin-top:1.25rem;">
                    <label class="p-label">Notas adicionales (opcional)</label>
                    <textarea class="p-input" id="f_notas" rows="2" placeholder="Alguna indicación especial para la entrega..."></textarea>
                </div>

                <div class="section-nav">
                    <button class="btn-prev" onclick="goToSection(2)"><i class="ri-arrow-left-line"></i>
                        Anterior</button>
                    <button class="btn-next" id="btn-confirm" onclick="confirmOrder()">
                        <i class="ri-check-double-line"></i> Confirmar Pedido
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- /#order-sections --}}
@endsection

@section('scripts')
    <script>
        /* ═══════════════════════════════════════════════════════
       State
    ═══════════════════════════════════════════════════════ */
        const selectedItems = {}; // { idproducto: { qty, price, name } }
        const PRICES = {}; // { idproducto: price }
        const LOYALTY_ELIGIBLE = {{ $loyalty['reward_eligible'] ? 'true' : 'false' }};
        let lowestRechargePrice = 0;
        let selectedPay = 'efectivo';
        let currentSection = 1;

        @foreach ($products as $p)
            PRICES[{{ $p->id }}] = {{ $p->precio_venta }};
            @if (stripos($p->descripcion, 'recarga') !== false)
                if (lowestRechargePrice === 0 || {{ $p->precio_venta }} < lowestRechargePrice) {
                    lowestRechargePrice = {{ $p->precio_venta }};
                }
            @endif
        @endforeach

        /* ═══════════════════════════════════════════════════════
           Wizard navigation
        ═══════════════════════════════════════════════════════ */
        function goToSection(n) {
            if (n === 2 && currentSection === 1) {
                /* always ok */ }
            if (n === 3 && Object.keys(selectedItems).length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin productos',
                    text: 'Debes seleccionar al menos un producto.',
                    confirmButtonColor: '#0b5ed7'
                });
                return;
            }

            document.querySelectorAll('.order-section').forEach(s => s.classList.remove('visible'));
            document.getElementById('section-' + n).classList.add('visible');

            document.querySelectorAll('.wizard-step').forEach((step, idx) => {
                const num = idx + 1;
                step.classList.remove('active', 'done');
                if (num < n) step.classList.add('done');
                else if (num === n) step.classList.add('active');
                if (num < n) step.querySelector('.step-num').innerHTML = '<i class="ri-check-line"></i>';
                else step.querySelector('.step-num').textContent = num;
            });

            currentSection = n;
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        /* ═══════════════════════════════════════════════════════
           Product selection
        ═══════════════════════════════════════════════════════ */
        function toggleProduct(id, price, name) {
            if (selectedItems[id]) {
                delete selectedItems[id];
                document.getElementById('prod-' + id).classList.remove('selected');
                document.getElementById('qty-ctrl-' + id).style.display = 'none';
            } else {
                selectedItems[id] = {
                    qty: 1,
                    price: price,
                    name: name
                };
                document.getElementById('prod-' + id).classList.add('selected');
                document.getElementById('qty-ctrl-' + id).style.display = 'flex';
            }
            updateSummary();
        }

        function changeQty(id, delta) {
            if (!selectedItems[id]) return;
            const newQty = Math.max(1, Math.min(50, selectedItems[id].qty + delta));
            selectedItems[id].qty = newQty;
            document.getElementById('qty-' + id).textContent = newQty;
            updateSummary();
        }

        function updateSummary() {
            const keys = Object.keys(selectedItems);
            const btn = document.getElementById('btn-next-2');
            const box = document.getElementById('order-summary');
            const itemsEl = document.getElementById('summary-items');

            if (keys.length === 0) {
                btn.disabled = true;
                box.style.display = 'none';
                return;
            }

            btn.disabled = false;
            box.style.display = 'block';

            let subtotal = 0,
                html = '';
            keys.forEach(id => {
                const item = selectedItems[id];
                const line = item.qty * item.price;
                subtotal += line;
                html += `<div class="summary-row">
                    <span>${item.name} × ${item.qty}</span>
                    <span>S/ ${line.toFixed(2)}</span>
                 </div>`;
            });

            // Discount: 1 free reward if eligible & has recarga
            let discount = 0;
            if (LOYALTY_ELIGIBLE && lowestRechargePrice > 0) {
                // Check if any recarga product is in cart
                const hasRecarga = keys.some(id => {
                    const name = selectedItems[id]?.name || '';
                    return name.toLowerCase().includes('recarga');
                });
                if (hasRecarga) discount = lowestRechargePrice;
            }

            const total = Math.max(0, subtotal - discount);
            itemsEl.innerHTML = html;

            const discEl = document.getElementById('summary-discount');
            if (discEl) discEl.textContent = '-S/ ' + discount.toFixed(2);

            document.getElementById('summary-total').textContent = 'S/ ' + total.toFixed(2);
        }

        /* ═══════════════════════════════════════════════════════
           Payment
        ═══════════════════════════════════════════════════════ */
        function selectPay(label, method) {
            document.querySelectorAll('.pay-option').forEach(l => l.classList.remove('selected'));
            label.classList.add('selected');
            selectedPay = method;

            document.getElementById('qr-yape').classList.remove('visible');
            document.getElementById('qr-plin').classList.remove('visible');

            if (method === 'yape') document.getElementById('qr-yape').classList.add('visible');
            if (method === 'plin') document.getElementById('qr-plin').classList.add('visible');
        }

        /* ═══════════════════════════════════════════════════════
           Map
        ═══════════════════════════════════════════════════════ */
        @if (!empty($mapsApiKey))
            let map, marker;

            function initMap() {
                // Default: Tarapoto
                const defaultPos = {
                    lat: -6.4806,
                    lng: -76.3616
                };
                map = new google.maps.Map(document.getElementById('gmap'), {
                    center: defaultPos,
                    zoom: 14,
                    disableDefaultUI: false,
                    mapTypeControl: false,
                    streetViewControl: false,
                });
                marker = new google.maps.Marker({
                    position: defaultPos,
                    map,
                    draggable: true,
                    title: 'Arrastra para ajustar'
                });

                const onMove = () => {
                    const pos = marker.getPosition();
                    document.getElementById('f_coordenadas').value = pos.lat().toFixed(6) + ',' + pos.lng().toFixed(6);
                };
                google.maps.event.addListener(marker, 'dragend', onMove);
                map.addListener('click', e => {
                    marker.setPosition(e.latLng);
                    onMove();
                });
                onMove();
            }
        @endif

        function syncAddressFromInput() {
            /* Could trigger geocoding here if Maps API is available */
        }

        /* ═══════════════════════════════════════════════════════
           Submit order
        ═══════════════════════════════════════════════════════ */
        function confirmOrder() {
            const direccion = document.getElementById('f_direccion').value.trim();
            const fecha = document.getElementById('f_fecha').value;

            if (!direccion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Dirección requerida',
                    text: 'Por favor ingresa tu dirección de entrega.',
                    confirmButtonColor: '#0b5ed7'
                });
                return;
            }
            if (!fecha) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fecha requerida',
                    text: 'Selecciona la fecha de entrega.',
                    confirmButtonColor: '#0b5ed7'
                });
                return;
            }
            if (Object.keys(selectedItems).length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin productos',
                    text: 'Debes seleccionar al menos un producto.',
                    confirmButtonColor: '#0b5ed7'
                });
                return;
            }

            const payload = {
                _token: '{{ csrf_token() }}',
                direccion_entrega: direccion,
                referencia: document.getElementById('f_referencia').value.trim(),
                coordenadas: document.getElementById('f_coordenadas').value,
                fecha_programada: fecha,
                franja_horaria: document.getElementById('f_franja').value,
                metodo_pago: selectedPay,
                notas: document.getElementById('f_notas').value.trim(),
                items: Object.entries(selectedItems).map(([id, d]) => ({
                    idproducto: id,
                    cantidad: d.qty
                })),
            };

            const btn = document.getElementById('btn-confirm');
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Procesando...';

            fetch('{{ route('cliente.order.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Pedido registrado!',
                            html: `<b>${data.msg}</b>${data.free_reward_applied ? '<br><br>🎁 ¡Premio de fidelidad aplicado! Un bidón gratis.' : ''}`,
                            confirmButtonColor: '#0b5ed7',
                            confirmButtonText: 'Ver mi pedido',
                        }).then(() => {
                            window.location.href = data.tracking_url;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.msg,
                            confirmButtonColor: '#0b5ed7'
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ri-check-double-line"></i> Confirmar Pedido';
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: 'No se pudo enviar el pedido. Verifica tu conexión.',
                        confirmButtonColor: '#0b5ed7'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-check-double-line"></i> Confirmar Pedido';
                });
        }
    </script>
    @if (!empty($mapsApiKey))
        <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&callback=initMap"></script>
    @endif
@endsection
