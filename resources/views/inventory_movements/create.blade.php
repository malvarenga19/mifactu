{{-- resources/views/inventory_movements/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Nuevo Movimiento de Inventario')

@section('content')
<div class="card shadow">
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-exchange-alt text-primary"></i> Nuevo Movimiento de Inventario
        </h4>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory_movements.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo de Movimiento *</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="entrada" {{ old('type') == 'entrada' ? 'selected' : '' }}>
                                <i class="fas fa-arrow-down"></i> Entrada (Compra, devolución de cliente)
                            </option>
                            <option value="salida" {{ old('type') == 'salida' ? 'selected' : '' }}>
                                <i class="fas fa-arrow-up"></i> Salida (Venta, pérdida)
                            </option>
                            <option value="ajuste" {{ old('type') == 'ajuste' ? 'selected' : '' }}>
                                <i class="fas fa-adjust"></i> Ajuste (Corrección de inventario)
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Producto *</label>
                        <select name="product_id" id="product_id" class="form-select" required>
                            <option value="">Seleccione producto...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-stock="{{ $product->stock }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} - Stock actual: {{ number_format($product->stock, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Cantidad *</label>
                        <input type="number" 
                               name="quantity" 
                               id="quantity" 
                               step="0.01"
                               class="form-control @error('quantity') is-invalid @enderror" 
                               value="{{ old('quantity') }}"
                               required>
                        <small id="stockWarning" class="text-danger" style="display: none;"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reference_type" class="form-label">Referencia</label>
                        <select name="reference_type" id="reference_type" class="form-select">
                            <option value="">Sin referencia</option>
                            <option value="invoice">Factura/Compra</option>
                            <option value="adjustment">Ajuste manual</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="reference_id_container" style="display: none;">
                        <label for="reference_id" class="form-label">Número de Referencia</label>
                        <input type="text" 
                               name="reference_id" 
                               id="reference_id" 
                               class="form-control" 
                               placeholder="Ej: Factura #001, OC #123">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas / Motivo</label>
                        <textarea name="notes" 
                                  id="notes" 
                                  rows="4" 
                                  class="form-control"
                                  placeholder="Ej: Compra a proveedor, Devolución, Merma, etc.">{{ old('notes') }}</textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Información:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Los movimientos registran automáticamente el stock antes y después</li>
                            <li>Las salidas no pueden superar el stock actual</li>
                            <li>Los ajustes requieren una nota explicativa</li>
                        </ul>
                    </div>
                    
                    <div id="stockInfo" class="alert alert-secondary" style="display: none;">
                        <strong>Stock actual del producto:</strong> <span id="currentStock">0</span> unidades
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('inventory_movements.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Registrar Movimiento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Mostrar stock del producto seleccionado
$('#product_id').on('change', function() {
    const selected = $(this).find('option:selected');
    const stock = selected.data('stock');
    
    if (stock !== undefined) {
        $('#stockInfo').show();
        $('#currentStock').text(parseFloat(stock).toLocaleString('es-SV', {minimumFractionDigits: 2}));
    } else {
        $('#stockInfo').hide();
    }
});

// Validar cantidad según tipo
$('#type, #quantity, #product_id').on('change keyup', function() {
    const type = $('#type').val();
    const quantity = parseFloat($('#quantity').val());
    const currentStock = parseFloat($('#product_id').find('option:selected').data('stock'));
    const warningDiv = $('#stockWarning');
    
    if (type === 'salida' && quantity > currentStock) {
        warningDiv.show();
        warningDiv.text(`⚠️ No puedes vender/salir ${quantity} unidades. Stock disponible: ${currentStock}`);
        $('#submitBtn').prop('disabled', true);
    } else {
        warningDiv.hide();
        $('#submitBtn').prop('disabled', false);
    }
});

// Mostrar/ocultar campo de referencia
$('#reference_type').on('change', function() {
    if ($(this).val()) {
        $('#reference_id_container').show();
    } else {
        $('#reference_id_container').hide();
        $('#reference_id').val('');
    }
});

// Mostrar container de referencia si ya tenía valor
if ($('#reference_type').val()) {
    $('#reference_id_container').show();
}
</script>
@endpush