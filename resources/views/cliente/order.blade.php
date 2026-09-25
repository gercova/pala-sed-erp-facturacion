@extends('cliente.layout')
@section('title', 'Nuevo Pedido')
@section('meta_description', 'Realiza tu pedido de agua purificada con entrega a domicilio.')
@section('extra-styles')
    <link rel="stylesheet" href="{{ asset('css/order-client.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
    <div class="page-header"
        style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.75rem; font-weight:700; color:#1e293b; margin-bottom:.25rem;">Nuevo Pedido</h1>
            <p style="color:#64748b; margin:0; font-size:.9rem;">Completa los pasos para registrar tu pedido de agua purificada.</p>
        </div>
        @if ($loyalty['reward_eligible'] ?? false)
            <div
                style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:.65rem 1rem; font-size:.82rem; color:#15803d; font-weight:600; display:flex; align-items:center; gap:.4rem;">
                🎁 ¡Tienes un bidón GRATIS disponible por fidelidad!
            </div>
        @endif
    </div>

    @php
        $distritos = [
            '220601' => 'Tarapoto',
            '220602' => 'Morales',
            '220603' => 'La Banda de Shilcayo',
            '220609' => 'Shapaja',
        ];
        $clientDistrito = $distritos[$client->ubigeo] ?? ($client->ubigeo ?: 'Tarapoto');
    @endphp

    {{-- ── Wizard: 2 Pasos ─── --}}
    <div class="wizard-steps" id="wizard-steps">
        <div class="wizard-step active" id="step-1" onclick="goToSection(1)">
            <div class="step-num">1</div>
            <div class="step-label">1. Mi Pedido</div>
        </div>
        <div class="step-sep"></div>
        <div class="wizard-step" id="step-2" onclick="goToSection(2)">
            <div class="step-num">2</div>
            <div class="step-label">2. Entrega y Pago</div>
        </div>
    </div>

    <div id="order-sections">

        {{-- ══ PASO 1: PRODUCTOS ══ --}}
        <div class="order-section visible" id="section-1">
            <div class="p-card">
                <div class="p-card-header"><i class="ri-shopping-basket-line"></i> Selecciona tus Productos</div>

                @if ($products->isEmpty())
                    <div style="text-align:center; padding:2rem; color:#94a3b8;">
                        <i class="ri-box-3-line" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        No hay productos disponibles por el momento.
                    </div>
                @else
                    <div class="products-grid" id="products-grid">
                        @foreach ($products as $product)
                            <div class="product-card" id="prod-{{ $product->id }}"
                                onclick="toggleProduct({{ $product->id }}, {{ $product->precio_venta }}, '{{ addslashes($product->descripcion) }}')">
                                <div class="selected-badge"><i class="ri-check-line"></i></div>
                                <div class="prod-name">{{ $product->descripcion }}</div>
                                <div class="prod-price">S/ {{ number_format($product->precio_venta, 2) }}</div>
                                <div class="prod-unit">{{ $product->unit->descripcion ?? 'Unidad' }}</div>
                                <div class="qty-control" id="qty-ctrl-{{ $product->id }}" style="display:none;"
                                    onclick="event.stopPropagation()">
                                    <button class="qty-btn" type="button" onclick="changeQty({{ $product->id }}, -1)">−</button>
                                    <span class="qty-display" id="qty-{{ $product->id }}">1</span>
                                    <button class="qty-btn" type="button" onclick="changeQty({{ $product->id }}, 1)">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Resumen ── --}}
                <div class="order-summary" id="order-summary" style="display:none; margin-top:1.5rem;">
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
                    <div></div>
                    <button class="btn-next" id="btn-next-1" onclick="goToSection(2)" disabled>
                        Siguiente — Entrega y pago <i class="ri-arrow-right-line"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ PASO 2: ENTREGA + MAPA GOOGLE EN TIEMPO REAL + PAGO ══ --}}
        <div class="order-section" id="section-2">
            <div class="p-card">

                {{-- Datos del cliente resumidos --}}
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:.85rem 1.1rem; margin-bottom:1.5rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem;">
                        <span style="font-weight:700; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:#64748b;">
                            <i class="ri-user-3-line text-primary"></i> Datos de Contacto
                        </span>
                        <span class="badge bg-light text-dark border" style="font-size:.75rem;">
                            <i class="ri-map-pin-2-fill text-danger"></i> {{ $clientDistrito }}
                        </span>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:.75rem; font-size:.85rem;">
                        <div>
                            <span style="color:#64748b; font-size:.75rem; display:block;">Cliente / Razón Social:</span>
                            <b style="color:#1e293b;">{{ $client->nombres }}</b>
                        </div>
                        <div>
                            <span style="color:#64748b; font-size:.75rem; display:block;">Documento:</span>
                            <span style="color:#1e293b;">{{ $client->nro_documento }}</span>
                        </div>
                        <div>
                            <span style="color:#64748b; font-size:.75rem; display:block;">Teléfono de contacto:</span>
                            <input type="text" class="p-input" id="f_telefono_contacto" value="{{ $client->telefono }}"
                                placeholder="Teléfono para coordinar" style="padding:.35rem .65rem; font-size:.85rem; height:auto; margin-top:.2rem;">
                        </div>
                    </div>
                </div>

                {{-- Dirección de entrega --}}
                <div class="p-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="ri-map-pin-2-line"></i> Dirección de Entrega</span>
                    <button type="button" id="btn-addr-locate" onclick="detectRealtimeLocation(true)"
                        style="background:none; border:none; color:#0b5ed7; font-size:.8rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:.3rem;"
                        title="Obtener dirección desde tu ubicación GPS">
                        <i class="ri-navigation-fill"></i> Usar mi ubicación actual
                    </button>
                </div>

                <div style="margin-bottom:1rem;">
                    <label class="p-label" for="f_direccion">Dirección de entrega *</label>
                    <input type="text" class="p-input" id="f_direccion" placeholder="Ej. Jr. Lima 200, Tarapoto"
                        value="{{ $client->direccion }}" oninput="syncAddressFromInput()">
                    <div id="addr-auto-badge" class="address-autofilled-badge" style="display:none;"></div>
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label class="p-label" for="f_referencia">Referencia (punto de referencia)</label>
                    <input type="text" class="p-input" id="f_referencia"
                        placeholder="Frente al parque, casa verde, portón negro..." value="{{ $client->referencia }}">
                </div>

                {{-- ══ MARCO DE GOOGLE MAPS CON UBICACIÓN EN TIEMPO REAL ══ --}}
                <div style="margin-bottom:1.5rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem; margin-bottom:.55rem;">
                        <label class="p-label" style="margin-bottom:0;">
                            <i class="ri-map-2-line text-primary"></i> Ubica tu dirección en el mapa
                        </label>
                        <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                            <button type="button" id="btn-realtime-gps" onclick="detectRealtimeLocation(true)"
                                class="btn-gps-realtime" title="Buscar y centrar en tu ubicación GPS actual">
                                <i class="ri-crosshair-2-line"></i> <span id="gps-btn-text">Buscar mi ubicación actual</span>
                            </button>
                            <div class="map-layer-btn-group">
                                <button type="button" class="btn-layer active" id="btn-layer-roadmap" onclick="switchMapLayer('roadmap')">🗺️ Mapa</button>
                                <button type="button" class="btn-layer" id="btn-layer-satellite" onclick="switchMapLayer('satellite')">🛰️ Satélite</button>
                            </div>
                        </div>
                    </div>

                    {{-- Buscador integrado en el mapa con autocompletado en vivo --}}
                    <div style="position:relative; margin-bottom:.4rem;">
                        <div style="display:flex; gap:.5rem;">
                            <div style="position:relative; flex:1;">
                                <i class="ri-search-line" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                                <input type="text" id="map-search-input" class="p-input" style="padding-left:2.2rem; padding-right:2rem; font-size:.85rem;"
                                    placeholder="Buscar calle, avenida o lugar en Tarapoto..."
                                    autocomplete="off"
                                    oninput="onSearchInput(this.value)"
                                    onkeydown="if(event.key==='Enter'){ event.preventDefault(); searchLocationOnMap(); }">
                                <button type="button" id="btn-clear-search" onclick="clearMapSearch()"
                                    style="display:none; position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; font-size:.9rem; cursor:pointer;"
                                    title="Limpiar búsqueda">✕</button>
                            </div>
                            <button type="button" onclick="searchLocationOnMap()" class="btn-map-search">
                                <i class="ri-search-2-line"></i> Buscar
                            </button>
                        </div>
                        {{-- Dropdown de sugerencias de búsqueda --}}
                        <div id="search-suggestions-box" class="map-search-suggestions"></div>
                    </div>

                    {{-- Accesos directos a zonas de atención --}}
                    <div style="display:flex; gap:.35rem; align-items:center; flex-wrap:wrap; margin-bottom:.65rem;">
                        <span style="font-size:.73rem; color:#64748b; font-weight:600;">
                            <i class="ri-map-pin-range-line text-primary"></i> Zonas:
                        </span>
                        <button type="button" class="btn-chip" onclick="goToZone(-6.4806, -76.3616, 'Tarapoto')">📍 Tarapoto</button>
                        <button type="button" class="btn-chip" onclick="goToZone(-6.4839, -76.3889, 'Morales')">📍 Morales</button>
                        <button type="button" class="btn-chip" onclick="goToZone(-6.4842, -76.3475, 'La Banda de Shilcayo')">📍 La Banda</button>
                        <button type="button" class="btn-chip" onclick="goToZone(-6.5961, -76.2731, 'Shapaja')">📍 Shapaja</button>
                    </div>

                    {{-- Marco del Mapa --}}
                    <div id="map-container" class="google-maps-frame">
                        <div id="gmap"></div>
                        {{-- Botón flotante para buscar ubicación actual estilo Google Maps --}}
                        <button type="button" class="gmap-floating-locate" id="btn-floating-locate"
                            onclick="detectRealtimeLocation(true)" title="Buscar y centrar en mi ubicación actual">
                            <i class="ri-crosshair-2-fill"></i>
                        </button>
                        <div id="map-loading-overlay" class="map-overlay-loading" style="display:none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status" style="width:1rem; height:1rem;"></div>
                            <span id="map-loading-text">Detectando tu ubicación en tiempo real...</span>
                        </div>
                    </div>

                    {{-- Barra de coordenadas --}}
                    <div class="map-coords-badge"
                        style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem; margin-top:.5rem; padding:.5rem .85rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-size:.8rem;">
                        <div style="display:flex; align-items:center; gap:.45rem; color:#334155;">
                            <span style="display:inline-block; width:9px; height:9px; border-radius:50%; background:#16a34a;" id="gps-status-dot"></span>
                            <b>Coordenadas:</b>
                            <span id="display-coords" style="font-family:monospace; color:#0b5ed7; font-weight:600;">-6.480600, -76.361600</span>
                        </div>
                        <div style="font-size:.76rem; color:#64748b;">
                            <i class="ri-information-line text-primary"></i> Haz clic en el mapa o arrastra el marcador rojo para afinar tu ubicación.
                        </div>
                    </div>

                    <input type="hidden" id="f_coordenadas" name="coordenadas" value="{{ $client->coordenadas ?? '' }}">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                    <div>
                        <label class="p-label" for="f_fecha">Fecha de entrega *</label>
                        <input type="date" class="p-input" id="f_fecha" min="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    <div>
                        <label class="p-label" for="f_franja">Franja horaria</label>
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
                    <p class="qr-instructions">Escanea el QR con tu app Yape, realiza el pago y muéstrale el comprobante a tu repartidor.</p>
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
                    <p class="qr-instructions">Escanea el QR con tu app Plin, realiza el pago y muéstrale el comprobante a tu repartidor.</p>
                </div>

                {{-- Notas ── --}}
                <div style="margin-top:1.25rem;">
                    <label class="p-label" for="f_notas">Notas adicionales (opcional)</label>
                    <textarea class="p-input" id="f_notas" rows="2" placeholder="Alguna indicación especial para el repartidor (ej. timbre malogrado, llamar antes)..."></textarea>
                </div>

                <div class="section-nav">
                    <button class="btn-prev" type="button" onclick="goToSection(1)"><i class="ri-arrow-left-line"></i> Anterior — Mi pedido</button>
                    <button class="btn-next" id="btn-confirm" type="button" onclick="confirmOrder()">
                        <i class="ri-check-double-line"></i> Confirmar Pedido
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- /#order-sections --}}
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
           Wizard navigation (2 Pasos)
        ═══════════════════════════════════════════════════════ */
        function goToSection(n) {
            if (n === 2 && Object.keys(selectedItems).length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin productos seleccionados',
                    text: 'Debes seleccionar al menos un producto para continuar.',
                    confirmButtonColor: '#0b5ed7'
                });
                return;
            }

            document.querySelectorAll('.order-section').forEach(s => s.classList.remove('visible'));
            const targetSection = document.getElementById('section-' + n);
            if (targetSection) targetSection.classList.add('visible');

            document.querySelectorAll('.wizard-step').forEach((step, idx) => {
                const num = idx + 1;
                step.classList.remove('active', 'done');
                if (num < n) {
                    step.classList.add('done');
                    step.querySelector('.step-num').innerHTML = '<i class="ri-check-line"></i>';
                } else if (num === n) {
                    step.classList.add('active');
                    step.querySelector('.step-num').textContent = num;
                } else {
                    step.querySelector('.step-num').textContent = num;
                }
            });

            currentSection = n;

            if (n === 2) {
                // Initialize / resize Google Maps frame
                setTimeout(() => {
                    initGoogleMapsFrame();
                }, 150);
            }

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
            const btn = document.getElementById('btn-next-1');
            const box = document.getElementById('order-summary');
            const itemsEl = document.getElementById('summary-items');

            if (keys.length === 0) {
                if (btn) btn.disabled = true;
                if (box) box.style.display = 'none';
                return;
            }

            if (btn) btn.disabled = false;
            if (box) box.style.display = 'block';

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
                const hasRecarga = keys.some(id => {
                    const name = selectedItems[id]?.name || '';
                    return name.toLowerCase().includes('recarga');
                });
                if (hasRecarga) discount = lowestRechargePrice;
            }

            const total = Math.max(0, subtotal - discount);
            if (itemsEl) itemsEl.innerHTML = html;

            const discEl = document.getElementById('summary-discount');
            if (discEl) discEl.textContent = '-S/ ' + discount.toFixed(2);

            const totalEl = document.getElementById('summary-total');
            if (totalEl) totalEl.textContent = 'S/ ' + total.toFixed(2);
        }

        /* ═══════════════════════════════════════════════════════
           Payment selection
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
           Marco de Google Maps con Ubicación en Tiempo Real
        ═══════════════════════════════════════════════════════ */
        let map = null;
        let deliveryMarker = null;
        let userGpsMarker = null;
        let googleRoadmapLayer = null;
        let googleSatelliteLayer = null;
        let isMapInitialized = false;

        // Custom Google red pin icon
        const googleDeliveryPinIcon = L.divIcon({
            className: 'google-delivery-pin',
            html: `
                <div style="position:relative; width:34px; height:46px; transform:translate(-17px, -46px); cursor:grab;">
                    <svg width="34" height="46" viewBox="0 0 34 46" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 0C7.6 0 0 7.6 0 17c0 12.8 17 29 17 29s17-16.2 17-29C34 7.6 26.4 0 17 0z" fill="#EA4335" stroke="#B31412" stroke-width="1"/>
                        <circle cx="17" cy="17" r="6" fill="#FFFFFF"/>
                        <ellipse cx="17" cy="45" rx="7" ry="2" fill="rgba(0,0,0,0.3)"/>
                    </svg>
                </div>
            `,
            iconSize: [0, 0],
            iconAnchor: [0, 0]
        });

        // Pulsing GPS user icon
        const realtimeGpsIcon = L.divIcon({
            className: 'realtime-gps-icon',
            html: `
                <div class="gps-pulse-marker" style="transform:translate(-12px, -12px);" title="Tu ubicación GPS actual">
                    <div class="gps-pulse-wave"></div>
                    <div class="gps-pulse-core"></div>
                </div>
            `,
            iconSize: [0, 0],
            iconAnchor: [0, 0]
        });

        function showMapLoading(text) {
            const overlay = document.getElementById('map-loading-overlay');
            const txt = document.getElementById('map-loading-text');
            if (overlay && txt) {
                txt.textContent = text || 'Cargando mapa...';
                overlay.style.display = 'flex';
            }
        }

        function hideMapLoading() {
            const overlay = document.getElementById('map-loading-overlay');
            if (overlay) overlay.style.display = 'none';
        }

        function initGoogleMapsFrame() {
            if (isMapInitialized) {
                if (map) {
                    map.invalidateSize();
                }
                return;
            }

            const mapEl = document.getElementById('gmap');
            if (!mapEl) return;

            // Default center: Tarapoto center
            let defaultLat = -6.4806;
            let defaultLng = -76.3616;

            // Check if client already has saved coordinates
            const savedCoords = document.getElementById('f_coordenadas')?.value?.trim();
            let hasSavedCoords = false;
            if (savedCoords && savedCoords.includes(',')) {
                const parts = savedCoords.split(',');
                const pLat = parseFloat(parts[0]);
                const pLng = parseFloat(parts[1]);
                if (!isNaN(pLat) && !isNaN(pLng)) {
                    defaultLat = pLat;
                    defaultLng = pLng;
                    hasSavedCoords = true;
                }
            }

            // Create Leaflet map instance
            map = L.map('gmap', {
                center: [defaultLat, defaultLng],
                zoom: hasSavedCoords ? 17 : 14,
                zoomControl: true
            });

            // Google Maps Roadmap tile layer
            googleRoadmapLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            });

            // Google Maps Satellite layer
            googleSatelliteLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            });

            // Add default layer
            googleRoadmapLayer.addTo(map);

            // Add delivery pin
            setDeliveryPin(defaultLat, defaultLng, false);

            // Click on map moves delivery pin
            map.on('click', function(e) {
                setDeliveryPin(e.latlng.lat, e.latlng.lng, true);
                reverseGeocode(e.latlng.lat, e.latlng.lng, true);
            });

            isMapInitialized = true;

            setTimeout(() => {
                map.invalidateSize();
            }, 200);

            // If no saved coordinates, automatically trigger real-time location detection
            if (!hasSavedCoords) {
                detectRealtimeLocation(true);
            } else {
                // If saved coordinates exist, still try to detect GPS in background to show blue dot
                detectRealtimeLocation(false);
            }
        }

        function setDeliveryPin(lat, lng, updateAddress) {
            const formattedLat = parseFloat(lat).toFixed(6);
            const formattedLng = parseFloat(lng).toFixed(6);

            // Update hidden input
            const coordInput = document.getElementById('f_coordenadas');
            if (coordInput) coordInput.value = `${formattedLat},${formattedLng}`;

            // Update display badge
            const coordDisplay = document.getElementById('display-coords');
            if (coordDisplay) coordDisplay.textContent = `${formattedLat}, ${formattedLng}`;

            if (deliveryMarker) {
                deliveryMarker.setLatLng([lat, lng]);
            } else if (map) {
                deliveryMarker = L.marker([lat, lng], {
                    icon: googleDeliveryPinIcon,
                    draggable: true,
                    title: 'Punto de entrega (arrastra para mover)'
                }).addTo(map);

                deliveryMarker.on('dragend', function() {
                    const pos = deliveryMarker.getLatLng();
                    setDeliveryPin(pos.lat, pos.lng, true);
                    reverseGeocode(pos.lat, pos.lng, true);
                });
            }
        }

        function switchMapLayer(layer) {
            if (!map) return;
            document.querySelectorAll('.map-layer-btn-group .btn-layer').forEach(b => b.classList.remove('active'));

            if (layer === 'satellite') {
                if (map.hasLayer(googleRoadmapLayer)) map.removeLayer(googleRoadmapLayer);
                googleSatelliteLayer.addTo(map);
                document.getElementById('btn-layer-satellite')?.classList.add('active');
            } else {
                if (map.hasLayer(googleSatelliteLayer)) map.removeLayer(googleSatelliteLayer);
                googleRoadmapLayer.addTo(map);
                document.getElementById('btn-layer-roadmap')?.classList.add('active');
            }
        }

        /* ── Real-time GPS detection ── */
        function detectRealtimeLocation(zoomToUser = true) {
            const btn = document.getElementById('btn-realtime-gps');
            const btnText = document.getElementById('gps-btn-text');
            const floatingBtn = document.getElementById('btn-floating-locate');

            if (btn) btn.classList.add('locating');
            if (floatingBtn) floatingBtn.classList.add('locating');
            if (btnText) btnText.textContent = 'Detectando ubicación...';
            showMapLoading('Detectando tu ubicación en tiempo real vía GPS...');

            if (!navigator.geolocation) {
                hideMapLoading();
                if (btn) btn.classList.remove('locating');
                if (floatingBtn) floatingBtn.classList.remove('locating');
                if (btnText) btnText.textContent = 'Buscar mi ubicación actual';
                Swal.fire({
                    icon: 'warning',
                    title: 'GPS no disponible',
                    text: 'Tu navegador no soporta geolocalización. Puedes buscar tu calle escribiendo en el buscador o hacer clic directamente en el mapa.',
                    confirmButtonColor: '#0b5ed7'
                });
                return;
            }

            navigator.geolocation.getCurrentPosition(
                position => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = Math.round(position.coords.accuracy || 0);

                    hideMapLoading();
                    if (btn) btn.classList.remove('locating');
                    if (floatingBtn) floatingBtn.classList.remove('locating');
                    if (btnText) btnText.textContent = '¡Ubicación encontrada! ✓';
                    if (btn) btn.style.background = '#16a34a';

                    // Update or create blue GPS user marker (pulsing circle)
                    if (userGpsMarker) {
                        userGpsMarker.setLatLng([lat, lng]);
                    } else if (map) {
                        userGpsMarker = L.marker([lat, lng], {
                            icon: realtimeGpsIcon,
                            interactive: false,
                            zIndexOffset: 1000
                        }).addTo(map);
                    }

                    // Move red delivery pin to current location
                    setDeliveryPin(lat, lng, true);

                    if (map && zoomToUser) {
                        map.flyTo([lat, lng], 17, { duration: 1.2 });
                    }

                    // Reverse geocode to get street address
                    reverseGeocode(lat, lng, true);

                    // Update status dot
                    const statusDot = document.getElementById('gps-status-dot');
                    if (statusDot) statusDot.style.background = '#16a34a';

                    // Reset button text after 3.5s so user can click it anytime again
                    setTimeout(() => {
                        if (btnText) btnText.textContent = 'Buscar mi ubicación actual';
                        if (btn) btn.style.background = '#0b5ed7';
                    }, 3500);
                },
                error => {
                    hideMapLoading();
                    if (btn) btn.classList.remove('locating');
                    if (floatingBtn) floatingBtn.classList.remove('locating');
                    if (btnText) btnText.textContent = 'Buscar mi ubicación actual';
                    console.warn('Geolocation notice:', error);

                    if (zoomToUser) {
                        let msg = 'No se pudo obtener la señal GPS con alta precisión. Puedes buscar tu calle escribiendo en el buscador o hacer clic en una zona rápida (Tarapoto, Morales, La Banda).';
                        if (error.code === 1) msg = 'Permiso de ubicación denegado en el navegador. Puedes ingresar tu calle en el buscador o marcar tu casa directamente en el mapa.';
                        Swal.fire({
                            icon: 'info',
                            title: 'Ubicación actual',
                            text: msg,
                            confirmButtonColor: '#0b5ed7'
                        });
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        }

        /* ── Ir rápido a una zona / distrito ── */
        function goToZone(lat, lng, zoneName) {
            if (!map) return;
            showMapLoading(`Centrando en ${zoneName}...`);
            map.flyTo([lat, lng], 16, { duration: 1 });
            setDeliveryPin(lat, lng, true);
            reverseGeocode(lat, lng, true);
            setTimeout(() => {
                hideMapLoading();
            }, 600);
        }

        /* ── Reverse Geocoding (Coordinates -> Address) ── */
        function reverseGeocode(lat, lng, forceUpdate = false) {
            const dirInput = document.getElementById('f_direccion');
            const badge = document.getElementById('addr-auto-badge');

            if (badge) {
                badge.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Obteniendo dirección del mapa...';
                badge.style.display = 'inline-flex';
                badge.style.color = '#0b5ed7';
            }

            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=es`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data && data.address) {
                        const a = data.address;
                        const road = a.road || a.pedestrian || a.street || a.highway || '';
                        const houseNum = a.house_number ? ' #' + a.house_number : '';
                        const suburb = a.neighbourhood || a.suburb || a.residential || '';
                        const city = a.city || a.town || a.village || a.county || 'Tarapoto';

                        let formatted = '';
                        if (road) formatted += road + houseNum;
                        if (suburb && suburb !== road) formatted += (formatted ? ', ' : '') + suburb;
                        if (city && city !== suburb) formatted += (formatted ? ', ' : '') + city;

                        if (!formatted) formatted = data.display_name;

                        // Only overwrite if input was empty, user dragged/clicked pin, or was already auto-filled
                        if (dirInput && (!dirInput.value.trim() || dirInput.dataset.autoFilled === '1' || forceUpdate)) {
                            dirInput.value = formatted;
                            dirInput.dataset.autoFilled = '1';
                        }

                        if (badge) {
                            badge.innerHTML = '<i class="ri-checkbox-circle-fill"></i> Dirección detectada automáticamente del mapa';
                            badge.style.display = 'inline-flex';
                            badge.style.color = '#16a34a';
                        }
                    } else if (badge) {
                        badge.style.display = 'none';
                    }
                })
                .catch(err => {
                    console.warn('Geocoding error:', err);
                    if (badge) badge.style.display = 'none';
                });
        }

        /* ── Autocompletado de Búsqueda de Lugares en Vivo ── */
        let searchDebounceTimer = null;

        function onSearchInput(val) {
            const clearBtn = document.getElementById('btn-clear-search');
            const box = document.getElementById('search-suggestions-box');
            const query = val.trim();

            if (clearBtn) clearBtn.style.display = query.length > 0 ? 'block' : 'none';

            if (query.length < 2) {
                if (box) box.style.display = 'none';
                return;
            }

            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                fetchSearchSuggestions(query);
            }, 300);
        }

        function clearMapSearch() {
            const input = document.getElementById('map-search-input');
            const clearBtn = document.getElementById('btn-clear-search');
            const box = document.getElementById('search-suggestions-box');
            if (input) input.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            if (box) box.style.display = 'none';
        }

        function fetchSearchSuggestions(query) {
            const box = document.getElementById('search-suggestions-box');
            if (!box) return;

            let searchQuery = query;
            if (!query.toLowerCase().includes('tarapoto') && !query.toLowerCase().includes('san martin') && !query.toLowerCase().includes('peru')) {
                searchQuery += ', San Martín, Perú';
            }

            const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(searchQuery)}&limit=6&countrycodes=pe&accept-language=es`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        // Fallback without San Martin restriction
                        const fallbackUrl = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(query + ', Perú')}&limit=5&countrycodes=pe&accept-language=es`;
                        return fetch(fallbackUrl).then(r => r.json());
                    }
                    return data;
                })
                .then(items => {
                    if (!items || items.length === 0) {
                        box.innerHTML = `<div style="padding:.75rem 1rem; color:#94a3b8; font-size:.8rem; text-align:center;">
                            <i class="ri-map-pin-line" style="display:block; font-size:1.2rem; margin-bottom:.2rem;"></i>
                            No encontramos resultados para "${query}". Intenta con el nombre de la calle.
                        </div>`;
                        box.style.display = 'block';
                        return;
                    }

                    let html = '';
                    items.forEach(item => {
                        const parts = (item.display_name || '').split(',');
                        const mainTitle = parts[0] || item.name || query;
                        const subTitle = parts.slice(1, 4).join(',').trim();
                        const lat = parseFloat(item.lat);
                        const lng = parseFloat(item.lon);

                        html += `
                            <div class="suggestion-item" onclick="selectSuggestion(${lat}, ${lng}, '${addslashes(mainTitle)}', '${addslashes(item.display_name)}')">
                                <i class="ri-map-pin-2-fill suggestion-icon"></i>
                                <div>
                                    <div class="suggestion-title">${escapeHtml(mainTitle)}</div>
                                    <div class="suggestion-subtitle">${escapeHtml(subTitle)}</div>
                                </div>
                            </div>
                        `;
                    });

                    box.innerHTML = html;
                    box.style.display = 'block';
                })
                .catch(() => {
                    box.style.display = 'none';
                });
        }

        function selectSuggestion(lat, lng, shortTitle, fullDisplayName) {
            const box = document.getElementById('search-suggestions-box');
            const searchInput = document.getElementById('map-search-input');
            const dirInput = document.getElementById('f_direccion');
            const badge = document.getElementById('addr-auto-badge');

            if (box) box.style.display = 'none';
            if (searchInput) searchInput.value = shortTitle;

            setDeliveryPin(lat, lng, true);
            if (map) map.flyTo([lat, lng], 17, { duration: 1.2 });

            if (dirInput) {
                dirInput.value = shortTitle;
                dirInput.dataset.autoFilled = '1';
            }

            if (badge) {
                badge.innerHTML = '<i class="ri-checkbox-circle-fill"></i> Ubicación seleccionada en el mapa';
                badge.style.display = 'inline-flex';
                badge.style.color = '#16a34a';
            }

            reverseGeocode(lat, lng, false);
        }

        // Close suggestions on outside click
        document.addEventListener('click', function(e) {
            const box = document.getElementById('search-suggestions-box');
            if (box && !e.target.closest('#map-search-input') && !e.target.closest('#search-suggestions-box')) {
                box.style.display = 'none';
            }
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function addslashes(str) {
            return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
        }

        /* ── Forward Geocoding (Botón Buscar) ── */
        function searchLocationOnMap() {
            const input = document.getElementById('map-search-input');
            const query = input?.value?.trim();
            if (!query) return;

            showMapLoading('Buscando dirección en el mapa...');

            let searchQuery = query;
            if (!query.toLowerCase().includes('tarapoto') && !query.toLowerCase().includes('san martin') && !query.toLowerCase().includes('peru')) {
                searchQuery += ', San Martín, Perú';
            }

            const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(searchQuery)}&limit=1&accept-language=es`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    hideMapLoading();
                    if (data && data.length > 0) {
                        const item = data[0];
                        const lat = parseFloat(item.lat);
                        const lng = parseFloat(item.lon);

                        setDeliveryPin(lat, lng, true);
                        if (map) map.flyTo([lat, lng], 17, { duration: 1.2 });

                        const dirInput = document.getElementById('f_direccion');
                        if (dirInput) {
                            dirInput.value = query;
                            dirInput.dataset.autoFilled = '1';
                        }

                        const badge = document.getElementById('addr-auto-badge');
                        if (badge) {
                            badge.innerHTML = '<i class="ri-checkbox-circle-fill"></i> Dirección ubicada en el mapa';
                            badge.style.display = 'inline-flex';
                            badge.style.color = '#16a34a';
                        }

                        const box = document.getElementById('search-suggestions-box');
                        if (box) box.style.display = 'none';
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Dirección no encontrada',
                            text: 'No ubicamos la dirección exacta con el buscador. Puedes seleccionar tu zona con los botones rápidos (Tarapoto, Morales, La Banda) o ubicar tu casa sobre el mapa.',
                            confirmButtonColor: '#0b5ed7'
                        });
                    }
                })
                .catch(() => {
                    hideMapLoading();
                });
        }

        function syncAddressFromInput() {
            const dirInput = document.getElementById('f_direccion');
            if (dirInput) dirInput.dataset.autoFilled = '0';
            const badge = document.getElementById('addr-auto-badge');
            if (badge) badge.style.display = 'none';
        }

        /* ═══════════════════════════════════════════════════════
           Submit order
        ═══════════════════════════════════════════════════════ */
        function confirmOrder() {
            const direccion = document.getElementById('f_direccion')?.value?.trim();
            const fecha = document.getElementById('f_fecha')?.value;

            if (!direccion) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Dirección requerida',
                    text: 'Por favor ingresa tu dirección de entrega o ubícala en el mapa.',
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
                referencia: document.getElementById('f_referencia')?.value?.trim() || '',
                coordenadas: document.getElementById('f_coordenadas')?.value || '',
                telefono_contacto: document.getElementById('f_telefono_contacto')?.value?.trim() || '',
                fecha_programada: fecha,
                franja_horaria: document.getElementById('f_franja')?.value || 'flexible',
                metodo_pago: selectedPay,
                notas: document.getElementById('f_notas')?.value?.trim() || '',
                items: Object.entries(selectedItems).map(([id, d]) => ({
                    idproducto: id,
                    cantidad: d.qty
                })),
            };

            const btn = document.getElementById('btn-confirm');
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Registrando pedido...';

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
                            title: '¡Pedido registrado con éxito!',
                            html: `<b>${data.msg}</b>${data.free_reward_applied ? '<br><br>🎁 ¡Premio de fidelidad aplicado! Recibes un bidón gratis.' : ''}`,
                            confirmButtonColor: '#0b5ed7',
                            confirmButtonText: 'Ver estado de mi pedido',
                        }).then(() => {
                            window.location.href = data.tracking_url;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.msg || 'No se pudo registrar el pedido.',
                            confirmButtonColor: '#0b5ed7'
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ri-check-double-line"></i> Confirmar Pedido';
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo enviar el pedido. Verifica tu conexión a internet.',
                        confirmButtonColor: '#0b5ed7'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-check-double-line"></i> Confirmar Pedido';
                });
        }
    </script>
@endsection
