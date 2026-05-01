@extends('layouts.app')

@section('title', 'Nuevo Producto')
@section('breadcrumb', 'Productos / <strong>Nuevo</strong>')

@section('topbar-actions')
    <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">← Volver</a>
@endsection

@push('styles')
<style>
    .form-group { margin-bottom: 1.5rem; }
    .form-label { font-weight: 500; margin-bottom: 0.3rem; display: block; }
    .input-group-text { background-color: var(--surface2); }
    .equivalent-row { margin-bottom: 0.5rem; }
    .remove-equivalent { background: none; border: 1px solid var(--border); color: var(--danger); transition: all 0.15s; }
    .remove-equivalent:hover { background: rgba(255,92,92,0.1); border-color: var(--danger); }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="productForm">
    @csrf

    <div style="display:grid; grid-template-columns: 1fr 300px; gap:1.5rem; align-items:start">

        {{-- Columna principal --}}
        <div style="display:flex; flex-direction:column; gap:1.5rem">

            {{-- Información básica --}}
            <div class="card">
                <div class="card-title" style="margin-bottom:1.2rem">◈ Información general</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Código</label>
                        <input type="text" name="code" value="{{ old('code') }}" placeholder="SKU / Código interno">
                        <span class="form-hint">Código único del producto (opcional)</span>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select name="category_id">
                            <option value="">Seleccionar categoría…</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Proveedor</label>
                        <select name="supplier_id">
                            <option value="">Seleccionar proveedor…</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ubicación</label>
                        <input type="text" name="location" value="{{ old('location') }}" placeholder="Ej: Estante A, Fila 2">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" rows="2" placeholder="Descripción del producto...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Precios y stock --}}
            <div class="card">
                <div class="card-title" style="margin-bottom:1.2rem">◉ Precios y stock</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Precio de costo</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="cost_price" step="0.01" value="{{ old('cost_price') }}">
                        </div>
                        @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio de venta *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="sale_price" step="0.01" value="{{ old('sale_price') }}" required>
                        </div>
                        @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stock actual *</label>
                        <input type="number" name="stock" step="0.01" value="{{ old('stock', 0) }}" required>
                        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock mínimo</label>
                        <input type="number" name="min_stock" step="0.01" value="{{ old('min_stock', 0) }}">
                        <span class="form-hint">Alertar cuando el stock esté por debajo</span>
                        @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Códigos equivalentes --}}
            <div class="card">
                <div class="card-title" style="margin-bottom:1rem">◈ Códigos equivalentes</div>
                <div id="equivalentsContainer">
                    <div class="equivalent-row" style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                        <input type="text" name="equivalent_codes[]" style="flex:1" placeholder="Código equivalente (ej: SKU-123, código de proveedor)">
                        <button type="button" class="remove-equivalent" style="display:none;" disabled>✕</button>
                    </div>
                </div>
                <button type="button" id="addEquivalent" class="btn btn-secondary btn-sm" style="margin-top:0.5rem;">+ Agregar otro código</button>
                <span class="form-hint">Códigos alternativos para buscar este producto</span>
            </div>
        </div>

        {{-- Panel lateral --}}
        <div style="position:sticky; top:1rem;">
            <div class="card">
                <div class="card-title" style="margin-bottom:1rem">◎ Imagen del producto</div>

                <div id="imagePreview" style="text-align:center; margin-bottom:1rem; min-height:150px; background:var(--surface2); border-radius:var(--radius); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:0.5rem;">
                    <span style="color:var(--muted); font-size:0.85rem;">Sin imagen</span>
                </div>

                <div class="form-group">
                    <input type="file" name="image_path" id="image_path" accept="image/*" style="padding:0.4rem;">
                    <span class="form-hint">Formatos: JPG, PNG, GIF (Máx. 2MB)</span>
                    @error('image_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <hr style="margin: 1rem 0;">

                <div style="display:flex; flex-direction:column; gap:0.6rem;">
                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">✓ Guardar producto</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary" style="width:100%; justify-content:center;">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Vista previa de imagen
    document.getElementById('image_path').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';

        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const img = document.createElement('img');
                img.src = ev.target.result;
                img.style.maxWidth = '100%';
                img.style.maxHeight = '180px';
                img.style.borderRadius = 'var(--radius)';
                img.style.objectFit = 'contain';
                preview.appendChild(img);
            };
            reader.readAsDataURL(e.target.files[0]);
        } else {
            preview.innerHTML = '<span style="color:var(--muted); font-size:0.85rem;">Sin imagen</span>';
        }
    });

    // Manejo de equivalencias
    let equivalentCount = 1;

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.equivalent-row');
        rows.forEach((row, idx) => {
            const btn = row.querySelector('.remove-equivalent');
            if (rows.length === 1) {
                btn.style.display = 'none';
                btn.disabled = true;
            } else {
                btn.style.display = 'inline-flex';
                btn.disabled = false;
            }
        });
    }

    document.getElementById('addEquivalent').addEventListener('click', () => {
        const container = document.getElementById('equivalentsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'equivalent-row';
        newRow.style.display = 'flex';
        newRow.style.gap = '0.5rem';
        newRow.style.marginBottom = '0.5rem';
        newRow.innerHTML = `
            <input type="text" name="equivalent_codes[]" style="flex:1" placeholder="Código equivalente">
            <button type="button" class="remove-equivalent" style="background:none; border:1px solid var(--border); color:var(--danger); border-radius:var(--radius); cursor:pointer; padding:0 0.6rem;">✕</button>
        `;
        container.appendChild(newRow);
        newRow.querySelector('.remove-equivalent').addEventListener('click', function() {
            newRow.remove();
            updateRemoveButtons();
        });
        updateRemoveButtons();
    });

    document.querySelectorAll('.remove-equivalent').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('.equivalent-row');
            if (row && document.querySelectorAll('.equivalent-row').length > 1) {
                row.remove();
                updateRemoveButtons();
            }
        });
    });

    updateRemoveButtons();

    // Validación simple
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="name"]').value.trim();
        const salePrice = document.querySelector('input[name="sale_price"]').value;

        if (!name) {
            e.preventDefault();
            alert('El nombre del producto es requerido');
            document.querySelector('input[name="name"]').focus();
        }

        if (!salePrice || parseFloat(salePrice) <= 0) {
            e.preventDefault();
            alert('El precio de venta debe ser mayor a 0');
            document.querySelector('input[name="sale_price"]').focus();
        }
    });
</script>
@endpush