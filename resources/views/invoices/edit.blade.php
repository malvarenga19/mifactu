@extends('layouts.app')

@section('title', isset($invoice) ? 'Editar Factura' : 'Nueva Factura')
@section('breadcrumb', 'Facturas / <strong>' . (isset($invoice) ? 'Editar ' . $invoice->correlative : 'Nueva') . '</strong>')

@section('topbar-actions')
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm">← Volver</a>
@endsection

@push('styles')
    <style>
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .items-table th {
            font-family: var(--mono);
            font-size: 0.68rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
            padding: .5rem .7rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }

        .items-table td {
            padding: .45rem .6rem;
            border-bottom: 1px solid var(--border);
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table input,
        .items-table select {
            padding: .35rem .55rem;
            font-size: 0.83rem;
        }

        .remove-row {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 1rem;
            padding: .25rem .4rem;
            border-radius: var(--radius);
            transition: background .15s;
        }

        .remove-row:hover {
            background: rgba(255, 92, 92, .1);
        }

        #totals-panel {
            background: var(--surface2);
            border-radius: var(--radius);
            padding: 1rem;
            font-size: 0.88rem;
        }

        .tot-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: .45rem;
        }

        .tot-row span:last-child {
            font-family: var(--mono);
        }

        .tot-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent);
        }

        .credit-days-group {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: var(--surface1);
            border-radius: var(--radius);
            display: none;
        }

        .credit-days-group label {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        .credit-days-group input {
            font-size: 0.9rem;
        }

        .non-inventory-row {
            background-color: rgba(255, 193, 7, 0.05);
        }

        .non-inventory-badge {
            font-size: 0.7rem;
            background: var(--warn);
            color: #000;
            padding: 0.15rem 0.4rem;
            border-radius: 12px;
            margin-left: 0.5rem;
        }
    </style>
@endpush

@section('content')
    @php $editing = isset($invoice); @endphp

    @if($errors->hasAny(array_filter(array_keys($errors->toArray()), fn($k) => str_starts_with($k, 'items.stock.'))))
        <div class="alert alert-error" style="margin-bottom:1.2rem">
            <strong>Stock insuficiente — no se guardó la factura:</strong><br>
            @foreach($errors->toArray() as $key => $msgs)
                @if(str_starts_with($key, 'items.stock.'))
                    @foreach($msgs as $msg)
                        <div style="margin-top:.3rem">⚠ {{ $msg }}</div>
                    @endforeach
                @endif
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('invoices.update', $invoice) : route('invoices.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start">

            {{-- Columna principal --}}
            <div style="display:flex;flex-direction:column;gap:1.5rem">

                {{-- Datos generales --}}
                <div class="card">
                    <div class="card-title" style="margin-bottom:1.2rem">◈ Datos generales</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tipo de factura *</label>
                            <select name="invoice_type_id" id="invoice_type_id" required>
                                <option value="">Seleccionar…</option>
                                @foreach($invoiceTypes as $type)
                                    <option value="{{ $type->id }}" data-code="{{ $type->code }}"
                                        @selected(old('invoice_type_id', $invoice->invoice_type_id ?? '') == $type->id)>
                                        {{ $type->name }} ({{ $type->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('invoice_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Cliente *</label>
                            <select name="customer_id" id="customer_id" required>
                                <option value="">Seleccionar…</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" data-retains="{{ $c->retains_iva }}"
                                        @selected(old('customer_id', $invoice->customer_id ?? '') == $c->id)>
                                        {{ $c->name }}{{ $c->company_name ? ' — ' . $c->company_name : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha de emisión *</label>
                            <input type="date" name="issue_date" id="issue_date" required
                                value="{{ old('issue_date', $invoice->issue_date ?? date('Y-m-d')) }}">
                            @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Fecha de vencimiento</label>
                            <input type="date" name="due_date" id="due_date" readonly
                                style="background-color:var(--surface2);cursor:pointer;"
                                value="{{ old('due_date', $invoice->due_date ?? '') }}"
                                title="Se calcula automáticamente según método de pago">
                            @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label>Método de pago *</label>
                            <select name="payment_method" id="payment_method" required>
                                @foreach(['cash' => 'Efectivo', 'credit_card' => 'Tarjeta de crédito', 'bank_transfer' => 'Transferencia bancaria', 'credit' => 'Crédito'] as $v => $l)
                                    <option value="{{ $v }}" @selected(old('payment_method', $invoice->payment_method ?? '') == $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Campo días crédito (se muestra solo cuando método de pago es crédito) --}}
                    <div class="credit-days-group" id="creditDaysGroup">
                        <div class="form-group" style="margin-bottom:0">
                            <label>Plazo de crédito (días) *</label>
                            <input type="number" id="credit_days" name="credit_days" min="1" max="365" step="1"
                                placeholder="Ej: 30" value="{{ old('credit_days', $invoice->credit_days ?? '') }}">
                            <small style="color:var(--muted);display:block;margin-top:0.25rem">
                                La fecha de vencimiento se calculará automáticamente
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nota / Observaciones</label>
                        <textarea name="note">{{ old('note', $invoice->note ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Ítems --}}
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">◉ Productos / Servicios</span>
                        <button type="button" id="add-row" class="btn btn-secondary btn-sm">+ Agregar línea</button>
                    </div>
                    @error('items')<div class="invalid-feedback" style="margin-bottom:.5rem">{{ $message }}</div>@enderror

                    <div class="table-wrap">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width:200px">Producto</th>
                                    <th>Descripción</th>
                                    <th style="width:80px">Cant.</th>
                                    <th style="width:110px">P. Unitario</th>
                                    <th style="width:110px">Total</th>
                                    <th style="width:60px;text-align:center">Exento</th>
                                    <th style="width:80px;text-align:center">No invent.</th>
                                    <th style="width:36px"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                @php
                                    $existingItems = old('items', $editing ? $invoice->items->toArray() : []);
                                @endphp
                                @forelse($existingItems as $idx => $item)
                                    <tr class="item-row {{ !empty($item['non_inventory']) ? 'non-inventory-row' : '' }}">
                                        <td>
                                            <select name="items[{{ $idx }}][product_id]" class="product-select" {{ !empty($item['non_inventory']) ? 'disabled' : '' }} required>
                                                <option value="">—</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" data-price="{{ $p->sale_price }}"
                                                        data-name="{{ $p->name }}" data-stock="{{ $p->stock ?? 0 }}"
                                                        @selected(($item['product_id'] ?? '') == $p->id)>
                                                        {{ $p->code ? '[' . $p->code . '] ' : '' }}{{ $p->name }}
                                                        (Stock: {{ $p->stock ?? 0 }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if(!empty($item['non_inventory']))
                                                <input type="hidden" name="items[{{ $idx }}][product_id]" value="">
                                                <input type="text" class="non-inv-name" placeholder="Nombre del servicio/producto"
                                                    value="{{ $item['description'] ?? '' }}"
                                                    style="margin-top:0.3rem;font-size:0.8rem;">
                                            @endif
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $idx }}][description]"
                                                value="{{ $item['description'] ?? '' }}" required placeholder="Descripción…">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $idx }}][quantity]" class="qty-input"
                                                value="{{ $item['quantity'] ?? 1 }}" min="0.01" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $idx }}][unit_price]" class="price-input"
                                                value="{{ $item['unit_price'] ?? '' }}" min="0" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="number" class="line-total" readonly value="{{ $item['total'] ?? '' }}"
                                                style="background:var(--bg);border-color:transparent;color:var(--accent);font-weight:700">
                                        </td>
                                        <td style="text-align:center">
                                            <input type="hidden" name="items[{{ $idx }}][exento]" value="0">
                                            <input type="checkbox" name="items[{{ $idx }}][exento]" value="1"
                                                class="exento-check" @checked(!empty($item['exento']))
                                                style="width:auto;accent-color:var(--warn)">
                                        </td>
                                        <td style="text-align:center">
                                            <input type="hidden" name="items[{{ $idx }}][non_inventory]" value="0">
                                            <input type="checkbox" name="items[{{ $idx }}][non_inventory]" value="1"
                                                class="non-inventory-check" @checked(!empty($item['non_inventory']))
                                                style="width:auto;accent-color:var(--info)">
                                        </td>
                                        <td>
                                            <button type="button" class="remove-row" title="Eliminar">✕</button>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- row vacío inicial --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Panel lateral --}}
            <div style="position:sticky;top:1rem;display:flex;flex-direction:column;gap:1rem">
                <div class="card">
                    <div class="card-title" style="margin-bottom:1rem">◎ Totales</div>
                    <div id="totals-panel">
                        <div class="tot-row"><span style="color:var(--muted)">Exento</span> <span id="t-exento">$0.00</span>
                        </div>
                        <div class="tot-row"><span style="color:var(--muted)">Gravado</span> <span
                                id="t-gravado">$0.00</span></div>
                        <div class="tot-row"><span style="color:var(--muted)">IVA 13%</span> <span id="t-iva">$0.00</span>
                        </div>
                        <div class="tot-row" id="row-ivar" style="display:none">
                            <span style="color:var(--warn)">IVA ret.</span>
                            <span id="t-ivar" style="color:var(--warn)">$0.00</span>
                        </div>
                        <hr style="border:none;border-top:1px solid var(--border);margin:.5rem 0">
                        <div class="tot-row">
                            <span
                                style="font-size:0.75rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em">Total</span>
                            <span id="t-total" class="tot-total">$0.00</span>
                        </div>
                    </div>
                    <div style="margin-top:1.2rem;display:flex;flex-direction:column;gap:.6rem">
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                            {{ $editing ? '✔ Guardar cambios' : '+ Crear factura' }}
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary"
                            style="width:100%;justify-content:center">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@php
    $productsJson = $products->map(function ($p) {
        return ['id' => $p->id, 'name' => $p->name, 'code' => $p->code, 'price' => $p->sale_price, 'stock' => $p->stock ?? 0];
    })->values()->toJson();
@endphp

@push('scripts')
    <script>
        const products = {!! $productsJson !!};

        let rowIdx = document.querySelectorAll('.item-row').length;

        function buildProductOptions(selectedId = '') {
            let opts = '<option value="">—</option>';
            products.forEach(p => {
                const label = (p.code ? '[' + p.code + '] ' : '') + p.name + ' (Stock: ' + p.stock + ')';
                opts += `<option value="${p.id}" data-price="${p.price}" data-name="${p.name}" data-stock="${p.stock}" ${p.id == selectedId ? 'selected' : ''}>
                        ${label}</option>`;
            });
            return opts;
        }

        function newRow(idx) {
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
                    <td style="position:relative">
                        <select name="items[${idx}][product_id]" class="product-select" required>${buildProductOptions()}</select>
                        <div class="non-inv-fields" style="display:none; margin-top:0.5rem;">
                            <input type="text" class="non-inv-name" placeholder="Nombre del servicio/producto" style="width:100%; font-size:0.8rem;">
                        </div>
                    </td>
                    <td><input type="text" name="items[${idx}][description]" required placeholder="Descripción…"></td>
                    <td><input type="number" name="items[${idx}][quantity]" class="qty-input" value="1" min="0.01" step="0.01" required></td>
                    <td><input type="number" name="items[${idx}][unit_price]" class="price-input" min="0" step="0.01" required></td>
                    <td><input type="number" class="line-total" readonly style="background:var(--bg);border-color:transparent;color:var(--accent);font-weight:700"></td>
                    <td style="text-align:center">
                        <input type="hidden" name="items[${idx}][exento]" value="0">
                        <input type="checkbox" name="items[${idx}][exento]" value="1" class="exento-check" style="width:auto;accent-color:var(--warn)">
                    </td>
                    <td style="text-align:center">
                        <input type="hidden" name="items[${idx}][non_inventory]" value="0">
                        <input type="checkbox" name="items[${idx}][non_inventory]" value="1" class="non-inventory-check" style="width:auto;accent-color:var(--info)">
                    </td>
                    <td><button type="button" class="remove-row" title="Eliminar">✕</button></td>`;
            return tr;
        }

        // Calcular fecha de vencimiento basado en fecha de emisión + días de crédito
        function calculateDueDate() {
            const paymentMethod = document.getElementById('payment_method').value;
            const issueDate = document.getElementById('issue_date').value;
            const creditDays = parseInt(document.getElementById('credit_days').value);
            const dueDateInput = document.getElementById('due_date');

            if (paymentMethod === 'credit' && creditDays && !isNaN(creditDays) && creditDays > 0 && issueDate) {
                const date = new Date(issueDate);
                date.setDate(date.getDate() + creditDays);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                dueDateInput.value = `${year}-${month}-${day}`;
                dueDateInput.style.backgroundColor = 'var(--surface1)';
            } else if (paymentMethod !== 'credit') {
                dueDateInput.value = '';
                dueDateInput.style.backgroundColor = 'var(--surface2)';
            } else if (!creditDays || isNaN(creditDays)) {
                dueDateInput.value = '';
            }
        }

        // Mostrar/ocultar campo de días de crédito
        function toggleCreditDays() {
            const paymentMethod = document.getElementById('payment_method').value;
            const creditGroup = document.getElementById('creditDaysGroup');

            if (paymentMethod === 'credit') {
                creditGroup.style.display = 'block';
                const creditDaysInput = document.getElementById('credit_days');
                if (!creditDaysInput.value && !{{ $editing ? 'true' : 'false' }}) {
                    creditDaysInput.value = '30';
                    calculateDueDate();
                } else {
                    calculateDueDate();
                }
            } else {
                creditGroup.style.display = 'none';
                document.getElementById('credit_days').value = '';
                calculateDueDate();
            }
        }

        document.getElementById('add-row').addEventListener('click', () => {
            const tr = newRow(rowIdx++);
            document.getElementById('items-body').appendChild(tr);
            bindRow(tr);
            recalc();
        });

        // Si no hay items al cargar, agregar uno vacío
        if (document.querySelectorAll('.item-row').length === 0) {
            const tr = newRow(rowIdx++);
            document.getElementById('items-body').appendChild(tr);
            bindRow(tr);
        }

        function toggleNonInventory(tr, isNonInv) {
            const productSelect = tr.querySelector('.product-select');
            const nonInvNameField = tr.querySelector('.non-inv-name');
            const descField = tr.querySelector('input[name$="[description]"]');
            const priceField = tr.querySelector('.price-input');

            if (isNonInv) {
                // Modo no inventariado
                productSelect.disabled = true;
                productSelect.style.opacity = '0.5';
                productSelect.value = '';

                // Crear o mostrar campo de nombre personalizado
                if (!nonInvNameField) {
                    const td = productSelect.closest('td');
                    const div = document.createElement('div');
                    div.className = 'non-inv-fields';
                    div.style.marginTop = '0.5rem';
                    div.innerHTML = '<input type="text" class="non-inv-name" placeholder="Nombre del servicio/producto" style="width:100%; font-size:0.8rem;">';
                    td.appendChild(div);
                } else {
                    nonInvNameField.style.display = 'block';
                }

                tr.classList.add('non-inventory-row');

                // Limpiar y permitir edición manual
                if (descField) descField.value = '';
                if (priceField) priceField.value = '';

            } else {
                // Modo inventariado
                productSelect.disabled = false;
                productSelect.style.opacity = '1';

                if (nonInvNameField) {
                    nonInvNameField.style.display = 'none';
                    nonInvNameField.value = '';
                }

                tr.classList.remove('non-inventory-row');

                // Si hay nombre personalizado, limpiarlo
                if (descField && descField.value && !productSelect.value) {
                    descField.value = '';
                }
            }

            // Recalcular línea
            const qty = tr.querySelector('.qty-input');
            const price = tr.querySelector('.price-input');
            const total = tr.querySelector('.line-total');
            calcRow(qty, price, total);
            recalc();
        }

        function bindRow(tr) {
            const $sel = $(tr).find('.product-select');
            const qty = tr.querySelector('.qty-input');
            const price = tr.querySelector('.price-input');
            const total = tr.querySelector('.line-total');
            const exent = tr.querySelector('.exento-check');
            const rem = tr.querySelector('.remove-row');
            const desc = tr.querySelector('input[name$="[description]"]');
            const nonInvCheck = tr.querySelector('.non-inventory-check');
            const nonInvNameField = tr.querySelector('.non-inv-name');

            // Inicializar estado no inventariado
            if (nonInvCheck && nonInvCheck.checked) {
                toggleNonInventory(tr, true);
            }

            // Inicializar Select2 en el select de producto de esta fila
            $sel.select2({
                placeholder: 'Buscar producto…',
                allowClear: true,
                width: '100%',
                dropdownParent: $('body'),
            });

            // Select2 dispara evento jQuery 'change'
            $sel.on('change', function () {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.dataset.price && !nonInvCheck.checked) {
                    price.value = parseFloat(opt.dataset.price).toFixed(2);
                    if (!desc.value && opt.dataset.name) desc.value = opt.dataset.name;
                }
                calcRow(qty, price, total);
                recalc();
            });

            // Evento para checkbox no inventariado
            if (nonInvCheck) {
                nonInvCheck.addEventListener('change', function (e) {
                    toggleNonInventory(tr, this.checked);
                });
            }

            // Evento para campo de nombre no inventariado
            if (nonInvNameField) {
                nonInvNameField.addEventListener('input', function () {
                    if (desc) desc.value = this.value;
                });
            }

            [qty, price].forEach(el => {
                if (el) el.addEventListener('input', () => {
                    calcRow(qty, price, total);
                    recalc();
                });
            });

            if (exent) exent.addEventListener('change', recalc);

            if (rem) {
                rem.addEventListener('click', () => {
                    $sel.select2('destroy');
                    tr.remove();
                    recalc();
                });
            }
        }

        function calcRow(qty, price, total) {
            if (!qty || !price || !total) return;
            const q = parseFloat(qty.value) || 0;
            const p = parseFloat(price.value) || 0;
            total.value = (q * p).toFixed(2);
        }

        function recalc() {
            const typeVal = $('#invoice_type_id').val();
            const typeCode = $('#invoice_type_id option[value="' + typeVal + '"]').data('code') ?? '';
            const esCF = typeCode === '01';

            const custVal = $('#customer_id').val();
            const retains = $('#customer_id option[value="' + custVal + '"]').data('retains') == 1;

            let subtotal = 0;
            let exento = 0;
            let baseImponible = 0;

            document.querySelectorAll('.item-row').forEach(tr => {
                const totalInput = tr.querySelector('.line-total');
                if (!totalInput) return;
                const t = parseFloat(totalInput.value) || 0;
                const isEx = tr.querySelector('.exento-check')?.checked;

                subtotal += t;

                if (isEx) {
                    exento += t;
                } else {
                    // Base imponible real: precio/1.13
                    baseImponible += t / 1.13;
                }
            });

            const baseImponibleR = Math.round(baseImponible * 100) / 100;
            const montoIva = Math.round(baseImponibleR * 0.13 * 100) / 100;

            // ✅ RETENCIÓN SOLO SI BASE IMPONIBLE >= 100
            // Esto significa subtotal >= 113 (100 × 1.13)
            let retencion = 0;
            if (retains && baseImponibleR >= 100) {
                retencion = Math.round(baseImponibleR * 0.01 * 100) / 100;
            }

            // Calcular valores mostrados según tipo de factura
            let gravadoMostrado, totalFinal;

            if (esCF) {
                gravadoMostrado = subtotal;
                totalFinal = Math.round((subtotal - retencion) * 100) / 100;
            } else {
                gravadoMostrado = baseImponibleR;
                totalFinal = Math.round(((baseImponibleR + montoIva) - retencion) * 100) / 100;
            }

            const fmt = v => '$' + v.toFixed(2);

            document.getElementById('t-exento').textContent = fmt(exento);
            document.getElementById('t-gravado').textContent = fmt(gravadoMostrado);
            document.getElementById('t-iva').textContent = fmt(montoIva);
            document.getElementById('t-ivar').textContent = retencion > 0 ? '-' + fmt(retencion) : '$0.00';
            document.getElementById('t-total').textContent = fmt(totalFinal);

            // Mostrar fila de retención solo si aplica
            document.getElementById('row-ivar').style.display = (retains && retencion > 0) ? 'flex' : 'none';
        }

        // Bind rows existentes
        document.querySelectorAll('.item-row').forEach(bindRow);

        // Select2 para cliente y tipo de factura (globales)
        $('#customer_id').select2({ placeholder: 'Buscar cliente…', allowClear: true, width: '100%' });
        $('#invoice_type_id').select2({ placeholder: 'Seleccionar tipo…', allowClear: false, width: '100%', minimumResultsForSearch: Infinity });

        // Eventos para cálculo automático de fecha de vencimiento
        document.getElementById('payment_method').addEventListener('change', toggleCreditDays);
        document.getElementById('issue_date').addEventListener('change', calculateDueDate);
        document.getElementById('credit_days').addEventListener('input', calculateDueDate);

        // Select2 dispara evento jQuery
        $('#customer_id').on('change', recalc);
        $('#invoice_type_id').on('change', function () {
            recalc();
        });

        // Inicializar estado del campo crédito
        toggleCreditDays();
        recalc();
    </script>
@endpush