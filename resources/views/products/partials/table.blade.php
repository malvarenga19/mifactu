{{-- resources/views/products/partials/table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="50">ID</th>
                <th>Nombre/Código</th>
                <th>Equivalencias</th>
                <th>Descripción</th>
                <th>Precio Venta</th>
                <th>Stock</th>
                <th width="120">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        @if($product->code)
                            <br>
                            <small class="text-muted">Código: {{ $product->code }}</small>
                        @endif
                    </td>
                    <td>
                        @if($product->equivalents->count())
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($product->equivalents as $equivalent)
                                    <span class="badge bg-info text-dark">
                                        {{ $equivalent->equivalent_code }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">Sin equivalentes</span>
                        @endif
                    </td>
                    <td>{{ $product->description ?? '—' }}</td>
                    <td class="text-success fw-bold">$ {{ number_format($product->sale_price, 2) }}</td>
                    <td>
                        @if($product->stock <= 0)
                            <span class="badge bg-danger">Agotado</span>
                        @elseif($product->stock <= $product->min_stock)
                            <span class="badge bg-warning">{{ $product->stock }}</span>
                        @else
                            <span class="badge bg-success">{{ $product->stock }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('products.show', $product) }}" 
                               class="btn btn-info" 
                               title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" 
                               class="btn btn-warning" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    title="Eliminar"
                                    onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <form id="delete-form-{{ $product->id }}" 
                              action="{{ route('products.destroy', $product) }}" 
                              method="POST" 
                              style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">No se encontraron productos</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <label class="text-muted small">Mostrar</label>
            <select id="perPageSelect" class="form-select form-select-sm d-inline-block w-auto">
                <option value="10" {{ $products->perPage() == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $products->perPage() == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $products->perPage() == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $products->perPage() == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-muted small">registros por página</span>
        </div>
        <div>
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    if (confirm(`¿Estás seguro de eliminar el producto "${name}"?\n\nEsta acción eliminará también todas sus equivalencias.`)) {
        document.getElementById(`delete-form-${id}`).submit();
    }
}

// Actualizar el valor de per_page en los enlaces de paginación
$('#perPageSelect').on('change', function() {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', $(this).val());
    window.location.href = currentUrl.toString();
});
</script>