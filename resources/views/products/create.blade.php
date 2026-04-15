@extends('layouts.app')

@section('title', 'Nuevo Producto')

@section('content')
<div class="card shadow">
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-plus-circle text-success"></i> Nuevo Producto
        </h4>
    </div>
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre *</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label">Código</label>
                        <input type="text" 
                               name="code" 
                               id="code" 
                               class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code') }}">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Código único del producto (opcional)</small>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Categoría</label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">Seleccione categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Proveedor</label>
                        <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                            <option value="">Seleccione proveedor</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Ubicación</label>
                        <input type="text" 
                               name="location" 
                               id="location" 
                               class="form-control @error('location') is-invalid @enderror" 
                               value="{{ old('location') }}"
                               placeholder="Ej: Estante A, Fila 2">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3" 
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Descripción del producto...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cost_price" class="form-label">Precio de Costo</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           name="cost_price" 
                                           id="cost_price" 
                                           step="0.01" 
                                           class="form-control @error('cost_price') is-invalid @enderror" 
                                           value="{{ old('cost_price') }}">
                                </div>
                                @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sale_price" class="form-label">Precio de Venta *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           name="sale_price" 
                                           id="sale_price" 
                                           step="0.01" 
                                           class="form-control @error('sale_price') is-invalid @enderror" 
                                           value="{{ old('sale_price') }}"
                                           required>
                                </div>
                                @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock *</label>
                                <input type="number" 
                                       name="stock" 
                                       id="stock" 
                                       class="form-control @error('stock') is-invalid @enderror" 
                                       value="{{ old('stock', 0) }}"
                                       required>
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_stock" class="form-label">Stock Mínimo</label>
                                <input type="number" 
                                       name="min_stock" 
                                       id="min_stock" 
                                       class="form-control @error('min_stock') is-invalid @enderror" 
                                       value="{{ old('min_stock', 0) }}">
                                @error('min_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Alertar cuando el stock esté por debajo</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image_path" class="form-label">Imagen del Producto</label>
                        <input type="file" 
                               name="image_path" 
                               id="image_path" 
                               class="form-control @error('image_path') is-invalid @enderror"
                               accept="image/*">
                        @error('image_path')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Formatos: JPG, PNG, GIF (Máx. 2MB)</small>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Vista previa de imagen
    document.getElementById('image_path').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '200px';
                img.style.maxHeight = '200px';
                img.className = 'img-thumbnail mt-2';
                preview.appendChild(img);
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    
    // Validación
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const salePrice = document.getElementById('sale_price').value;
        
        if (!name) {
            e.preventDefault();
            alert('El nombre del producto es requerido');
            document.getElementById('name').focus();
        }
        
        if (!salePrice || salePrice <= 0) {
            e.preventDefault();
            alert('El precio de venta debe ser mayor a 0');
            document.getElementById('sale_price').focus();
        }
    });
</script>
@endpush