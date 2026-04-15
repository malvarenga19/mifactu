<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio Costo</th>
                <th>Precio Venta</th>
                <th>Stock</th>
                <th>Stock Mínimo</th>
                <th>Ubicación</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->code ?? '—' }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->description }}</td>
                    <td class="text-end">$ {{ number_format($product->cost_price, 2) }}</td>
                    <td class="text-end">$ {{ number_format($product->sale_price, 2) }}</td>
                    <td>
                        @if($product->stock <= 0)
                            <span class="badge bg-danger">Agotado</span>
                        @elseif($product->stock <= $product->min_stock)
                            <span class="badge bg-warning text-dark">{{ $product->stock }}</span>
                        @else
                            <span class="badge bg-success">{{ $product->stock }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $product->min_stock ?? 0 }}</td>
                    <td>{{ $product->location ?? '—' }}</td>
                    <td>
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" 
                                 alt="{{ $product->name }}" 
                                 style="width: 40px; height: 40px; object-fit: cover;"
                                 class="rounded">
                        @else
                            <i class="fas fa-box fa-2x text-muted"></i>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('products.show', $product) }}" 
                               class="btn btn-sm btn-info" 
                               title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" 
                               class="btn btn-sm btn-warning" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-danger" 
                                    title="Eliminar"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal{{ $product->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <!-- Modal Eliminar -->
                        <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirmar eliminación</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        ¿Está seguro que desea eliminar el producto <strong>{{ $product->name }}</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                        <p class="mb-0">No se encontraron productos</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
@if($products->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted">
                Mostrando {{ $products->firstItem() ?? 0 }} a {{ $products->lastItem() ?? 0 }} 
                de {{ $products->total() }} productos
            </small>
            
            <div class="ms-3 d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Mostrar:</label>
                <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <small class="text-muted">por página</small>
            </div>
        </div>
        <div>
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
@endif