@extends('layouts.app')

@section('title', 'Nueva Factura')

@push('styles')
    {{-- Tom Select (búsqueda en selects) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <style>
        /* ── Tom Select overrides ─── */
        .ts-wrapper.form-control,
        .ts-wrapper.form-select {
            padding: 0;
        }

        .ts-control {
            border: 1px solid var(--rule) !important;
            border-radius: var(--radius) !important;
            background: var(--paper) !important;
            font-family: var(--sans);
            font-size: .875rem;
            color: var(--ink);
            padding: 6px 10px !important;
            box-shadow: none !important;
            min-height: 38px;
        }

        .ts-wrapper.focus .ts-control {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(184, 146, 42, .15) !important;
        }

        .ts-dropdown {
            border: 1px solid var(--rule) !important;
            border-radius: var(--radius) !important;
            box-shadow: var(--shadow-md) !important;
            font-size: .875rem;
            font-family: var(--sans);
        }

        .ts-dropdown .option {
            padding: 8px 12px;
        }

        .ts-dropdown .option.selected,
        .ts-dropdown .option:hover {
            background: var(--accent-lt) !important;
            color: var(--ink) !important;
        }

        .ts-dropdown .option .opt-sub {
            font-size: .73rem;
            color: var(--muted);
            font-family: var(--mono);
            margin-top: 1px;
        }

        /* Dentro de la tabla de ítems, más compacto */
        .items-table .ts-control {
            font-size: .82rem !important;
            padding: 4px 7px !important;
            min-height: 32px;
        }

        .items-table .ts-wrapper {
            min-width: 150px;
            width: 100%;
            display: block;
        }

        /* Forzar que la celda de producto ocupe todo el ancho disponible */
        .items-table td:first-child {
            width: 100%;
            min-width: 170px;
        }

        /* ── Layout dos columnas ─── */
        .invoice-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .invoice-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── Secciones del formulario ─── */
        .form-section {
            background: var(--paper);
            border: 1px solid var(--rule);
            border-radius: var(--radius);
            padding: 22px 24px;
            margin-bottom: 16px;
        }

        .form-section-title {
            font-family: var(--mono);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--rule);
        }

        /* ── Tabla de ítems ─── */
        .items-table th {
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 500;
            padding: 8px 10px;
            border-bottom: 1px solid var(--rule);
            white-space: nowrap;
        }

        .items-table td {
            padding: 6px 6px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(222, 218, 211, .4);
        }

        .items-table .form-control,
        .items-table .form-select {
            font-size: .82rem;
            padding: 5px 8px;
            height: auto;
        }

        .items-table .mono-input {
            font-family: var(--mono);
            text-align: right;
        }

        .item-total-cell {
            font-family: var(--mono);
            font-size: .85rem;
            text-align: right;
            white-space: nowrap;
            min-width: 90px;
            padding-right: 10px !important;
        }

        .btn-add-item {
            font-size: .82rem;
            font-family: var(--mono);
            letter-spacing: .04em;
        }

        .btn-remove-item {
            padding: 4px 8px;
            line-height: 1;
            font-size: .78rem;
        }

        /* ── Panel resumen ─── */
        .summary-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 7px 0;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            font-size: .85rem;
        }

        .summary-line:last-child {
            border-bottom: none;
        }

        .summary-line .label {
            color: rgba(232, 228, 220, .55);
        }

        .summary-line .value {
            font-family: var(--mono);
            font-size: .85rem;
        }

        .summary-line.total-line {
            margin-top: 10px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, .15);
            border-bottom: none;
        }

        .summary-line.total-line .label {
            color: rgba(232, 228, 220, .8);
            font-size: .9rem;
        }

        .summary-line.total-line .value {
            font-size: 1.4rem;
            color: #fff;
            font-weight: 500;
        }

        .exento-check {
            text-align: center;
            vertical-align: middle;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .exento-check input[type=checkbox] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }
    </style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Nueva Factura</h1>
            <p class="page-subtitle">Crear documento tributario</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
        @csrf

        <div class="invoice-grid">

            {{-- ── Columna principal ── --}}
            <div>

                {{-- 1. Encabezado del documento --}}
                <div class="form-section">
                    <div class="form-section-title">Encabezado del documento</div>
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                            <select name="invoice_type_id" id="invoice_type_id"
                                class="form-select @error('invoice_type_id') is-invalid @enderror" required>
                                <option value="">Seleccionar tipo…</option>
                                @foreach($invoiceTypes as $type)
                                    <option value="{{ $type->id }}" data-correlative="{{ $type->formatted_next_correlative }}"
                                        {{ old('invoice_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->code }} — {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('invoice_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Correlativo (próximo)</label>
                            <input type="text" id="preview_correlative" class="form-control"
                                placeholder="Seleccione tipo primero" readonly
                                style="font-family:var(--mono); background:var(--surface);">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Fecha de emisión <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date"
                                class="form-control @error('issue_date') is-invalid @enderror"
                                value="{{ old('issue_date', now()->format('Y-m-d')) }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Forma de pago <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror"
                                required>
                                <option value="">Seleccionar…</option>
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Efectivo
                                </option>
                                <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>
                                    Tarjeta de crédito</option>
                                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Transferencia bancaria</option>
                                <option value="credit" {{ old('payment_method') === 'credit' ? 'selected' : '' }}>Crédito
                                </option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Estado de pago <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror"
                                required>
                                <option value="pending" {{ old('payment_status', 'pending') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="paid" {{ old('payment_status') === 'paid' ? 'selected' : '' }}>Pagado</option>
                                <option value="overdue" {{ old('payment_status') === 'overdue' ? 'selected' : '' }}>Vencido
                                </option>
                            </select>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Fecha de vencimiento</label>
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                value="{{ old('due_date') }}">
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- 2. Cliente --}}
                <div class="form-section">
                    <div class="form-section-title">Cliente</div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Seleccionar cliente <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id"
                                class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Buscar cliente…</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-retains="{{ $customer->retains_iva ? '1' : '0' }}"
                                        data-doc="{{ $customer->document_number }}"
                                        data-sub="{{ $customer->document_number ? $customer->documentTypeName . ': ' . $customer->document_number : '' }}"
                                        {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->company_name ?: $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div id="customer_retains_notice" class="alert alert-warning py-2 mb-0 d-none" role="alert">
                                <i class="fas fa-triangle-exclamation me-2"></i>
                                Este cliente <strong>retiene IVA</strong>. El monto retenido se descontará del total.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Ítems --}}
                <div class="form-section">
                    <div class="form-section-title">Detalle de productos / servicios</div>

                    @error('items')
                        <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
                    @enderror

                    <div class="table-responsive">
                        <table class="table items-table mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="min-width:160px">Producto</th>
                                    <th style="min-width:200px">Descripción</th>
                                    <th style="width:100px">Cantidad</th>
                                    <th style="width:110px">Precio unit.</th>
                                    <th style="width:60px" class="text-center">Exento</th>
                                    <th style="width:100px" class="text-end">Total</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- fila inicial --}}
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">Seleccionar…</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}"
                                                    data-name="{{ $product->name }}" data-code="{{ $product->code ?? '' }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][description]"
                                            class="form-control item-description" required placeholder="Descripción…">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]"
                                            class="form-control mono-input item-qty" value="1" min="1" step="1"
                                            required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][unit_price]"
                                            class="form-control mono-input item-price" value="0.00" min="0.01" step="0.01"
                                            required>
                                    </td>
                                    <td class="exento-check">
                                        <input type="checkbox" name="items[0][exento]" class="item-exento" value="1">
                                    </td>
                                    <td class="item-total-cell">$0.00</td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-item"
                                            title="Eliminar fila">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="button" id="addItemBtn" class="btn btn-outline-secondary btn-sm btn-add-item">
                            <i class="fas fa-plus me-1"></i>Agregar ítem
                        </button>
                    </div>
                </div>

                {{-- 4. Nota --}}
                <div class="form-section">
                    <div class="form-section-title">Observaciones</div>
                    <textarea name="note" class="form-control" rows="3"
                        placeholder="Nota o comentario interno (opcional)…">{{ old('note') }}</textarea>
                </div>

            </div>{{-- /columna principal --}}

            {{-- ── Panel lateral: resumen --}}
            <div>
                <div class="summary-card" style="position: sticky; top: calc(var(--header-h) + 16px);">
                    <div class="summary-title">Resumen de montos</div>

                    <div class="summary-line">
                        <span class="label">Subtotal gravado</span>
                        <span class="value" id="s_gravado">$0.00</span>
                    </div>
                    <div class="summary-line">
                        <span class="label">Subtotal exento</span>
                        <span class="value" id="s_exento">$0.00</span>
                    </div>
                    <div class="summary-line">
                        <span class="label">IVA (13%)</span>
                        <span class="value" id="s_iva">$0.00</span>
                    </div>
                    <div class="summary-line" id="s_iva_ret_row" style="display:none">
                        <span class="label">IVA retenido</span>
                        <span class="value text-danger" id="s_iva_ret">-$0.00</span>
                    </div>
                    <div class="summary-line total-line">
                        <span class="label">Total</span>
                        <span class="value summary-total" id="s_total">$0.00</span>
                    </div>

                    <hr style="border-color:rgba(255,255,255,.1); margin: 16px 0;">

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-floppy-disk me-2"></i>Guardar factura
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary w-100 mt-2"
                        style="color: rgba(232,228,220,.6); border-color: rgba(255,255,255,.15);">
                        Cancelar
                    </a>
                </div>
            </div>

        </div>{{-- /invoice-grid --}}

    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        /* ──────────────────────────────────────────────────────────
           Variables globales
        ────────────────────────────────────────────────────────── */
        let rowIndex = 1;
        const IVA_RATE = 0.13;

        /* ── Datos de productos desde PHP (fuente única de verdad) ── */
        const PRODUCTS_DATA = {!! json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float) $p->sale_price, 'code' => $p->code ?? ''])->values()) !!};

        // Índice por id para lookup rápido
        const PRODUCTS = {};
        PRODUCTS_DATA.forEach(p => { PRODUCTS[p.id] = p; });

        /* ── Función que genera el <select> HTML para una nueva fila ── */
        function buildProductSelectHTML(index) {
            const opts = PRODUCTS_DATA.map(p =>
                `<option value="${p.id}" data-price="${p.price}" data-name="${p.name}" data-code="${p.code}">${p.name}</option>`
            ).join('');
            return `<option value="">Buscar producto…</option>${opts}`;
        }

        /* ──────────────────────────────────────────────────────────
           Tom Select — Cliente
        ────────────────────────────────────────────────────────── */
        const tsCustomer = new TomSelect('#customer_id', {
            placeholder: 'Buscar por nombre, empresa o documento…',
            searchField: ['text', 'sub'],
            maxOptions: 50,
            render: {
                option: function (data, escape) {
                    const sub = data.sub ? `<div class="opt-sub">${escape(data.sub)}</div>` : '';
                    return `<div>${escape(data.text)}${sub}</div>`;
                },
                item: function (data, escape) {
                    return `<div>${escape(data.text)}</div>`;
                }
            },
            onInitialize: function () {
                // Copiar data-* attributes a los items de Tom Select para poder leerlos
                const origSelect = document.querySelector('select[name="customer_id"]');
                origSelect.querySelectorAll('option').forEach(opt => {
                    const val = opt.value;
                    if (!val) return;
                    this.options[val] = {
                        ...this.options[val],
                        retains: opt.dataset.retains,
                        sub: opt.dataset.sub || '',
                    };
                });
            },
            onChange: function (value) {
                const opt = this.options[value] || {};
                const retains = opt.retains === '1';
                document.getElementById('customer_retains_notice').classList.toggle('d-none', !retains);
                recalcTotals();
            }
        });

        function initProductSelect(row) {
            const sel = row.querySelector('.product-select');
            if (!sel || sel.tomselect) return;

            const ts = new TomSelect(sel, {
                placeholder: 'Buscar por nombre o código…',
                searchField: ['text', 'code'],
                maxOptions: 40,
                dropdownParent: 'body',
                onInitialize: function () {
                    sel.querySelectorAll('option[value]').forEach(opt => {
                        if (!opt.value) return;
                        if (this.options[opt.value]) {
                            this.options[opt.value].code = opt.dataset.code || '';
                        }
                    });
                },
                render: {
                    option: function (data, escape) {
                        const p = PRODUCTS[data.value];
                        const code = p?.code ? `<span class="opt-sub">${escape(p.code)}</span>` : '';
                        const price = p ? `<span class="opt-sub">$${p.price.toFixed(2)}</span>` : '';
                        return `<div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                                        <span>${escape(data.text)} ${code}</span>${price}
                                    </div>`;
                    }
                },
                onChange: function (value) {
                    const p = PRODUCTS[value];
                    if (!p) return;
                    row.querySelector('.item-price').value = p.price.toFixed(2);
                    const desc = row.querySelector('.item-description');
                    if (!desc.value) desc.value = p.name;
                    updateRowTotal(row);
                }
            });
        }


        document.getElementById('invoice_type_id').addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const corr = opt.dataset.correlative || '';
            document.getElementById('preview_correlative').value = corr
                ? corr
                : '';
        });
        // Disparar al cargar si ya hay valor (old input)
        document.getElementById('invoice_type_id').dispatchEvent(new Event('change'));

        /* ──────────────────────────────────────────────────────────
           Correlativo preview al cambiar tipo
        ────────────────────────────────────────────────────────── */
        document.getElementById('addItemBtn').addEventListener('click', function () {
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
                    <td>
                        <select name="items[${rowIndex}][product_id]" class="form-select product-select" required>
                            ${buildProductSelectHTML(rowIndex)}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="items[${rowIndex}][description]"
                               class="form-control item-description" required placeholder="Descripción…">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIndex}][quantity]"
                               class="form-control mono-input item-qty"
                               value="1" min="1" step="1" required>
                    </td>
                    <td>
                        <input type="number" name="items[${rowIndex}][unit_price]"
                               class="form-control mono-input item-price"
                               value="0.00" min="0.01" step="0.01" required>
                    </td>
                    <td class="exento-check">
                        <input type="checkbox" name="items[${rowIndex}][exento]" class="item-exento" value="1">
                    </td>
                    <td class="item-total-cell">$0.00</td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-item" title="Eliminar">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </td>`;
            tbody.prepend(tr);
            bindRowEvents(tr);
            rowIndex++;
            recalcTotals();
        });

        /* ──────────────────────────────────────────────────────────
           Eliminar fila
        ────────────────────────────────────────────────────────── */
        document.getElementById('itemsBody').addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-item');
            if (!btn) return;
            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 1) return; // mantener al menos una
            btn.closest('tr').remove();
            recalcTotals();
        });

        /* ──────────────────────────────────────────────────────────
           Autocompletar precio al seleccionar producto
        ────────────────────────────────────────────────────────── */
        function bindRowEvents(row) {
            // Tom Select maneja el onChange del producto (ver initProductSelect)
            initProductSelect(row);

            row.querySelector('.item-qty').addEventListener('input', () => updateRowTotal(row));
            row.querySelector('.item-price').addEventListener('input', () => updateRowTotal(row));
            row.querySelector('.item-exento').addEventListener('change', () => recalcTotals());
        }

        // Vincular primera fila
        bindRowEvents(document.querySelector('.item-row'));

        /* ──────────────────────────────────────────────────────────
           Actualizar total de fila y luego recalcular panel
        ────────────────────────────────────────────────────────── */
        function updateRowTotal(row) {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = qty * price;
            row.querySelector('.item-total-cell').textContent = '$' + total.toFixed(2);
            recalcTotals();
        }

        /* ──────────────────────────────────────────────────────────
           Recalcular panel lateral
        ────────────────────────────────────────────────────────── */
        function recalcTotals() {
            let gravado = 0;
            let exento = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const isExento = row.querySelector('.item-exento').checked;
                const lineTotal = qty * price;
                if (isExento) { exento += lineTotal; }
                else { gravado += lineTotal; }
            });

            // IVA incluido en el precio (precio con IVA)
            const iva = gravado * IVA_RATE / (1 + IVA_RATE);

            // Retención — leer desde Tom Select
            const customerVal = tsCustomer.getValue();
            const customerOpt = tsCustomer.options[customerVal] || {};
            const retains = customerOpt.retains === '1';
            const ivaRet = retains ? iva : 0;

            const total = gravado + exento - ivaRet;

            // Actualizar DOM
            document.getElementById('s_gravado').textContent = '$' + gravado.toFixed(2);
            document.getElementById('s_exento').textContent = '$' + exento.toFixed(2);
            document.getElementById('s_iva').textContent = '$' + iva.toFixed(2);
            document.getElementById('s_iva_ret').textContent = '-$' + ivaRet.toFixed(2);
            document.getElementById('s_iva_ret_row').style.display = retains ? '' : 'none';
            document.getElementById('s_total').textContent = '$' + total.toFixed(2);
        }

        /* ──────────────────────────────────────────────────────────
           Validar al menos un ítem antes de enviar
        ────────────────────────────────────────────────────────── */
        document.getElementById('invoiceForm').addEventListener('submit', function (e) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un ítem a la factura.');
            }
        });
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('item-qty')) {
                if (e.target.value < 1) {
                    e.target.value = 1;

                }
            }
            if (e.target.classList.contains('item-price')) {
                if (e.target.value < 0.01) {
                    e.target.value = 0.01;
                }
            }
            recalcTotals();
        });
    </script>
@endpush