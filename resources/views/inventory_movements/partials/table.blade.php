{{-- resources/views/inventory_movements/partials/table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="50">ID</th>
                <th>Fecha/Hora</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Stock Anterior</th>
                <th>Stock Nuevo</th>
                <th>Referencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->id }}</td>
                    <td>
                        <small>
                            {{ $movement->created_at->format('d/m/Y') }}<br>
                            {{ $movement->created_at->format('H:i:s') }}
                        </small>
                    </td>
                    <td>
                        <strong>{{ $movement->product->name ?? 'Producto eliminado' }}</strong>
                        @if($movement->product && $movement->product->code)
                            <br><small class="text-muted">Código: {{ $movement->product->code }}</small>
                        @endif
                    </td>
                    <td>
                        @if($movement->type == 'entrada')
                            <span class="badge bg-success">
                                <i class="fas fa-arrow-down"></i> Entrada
                            </span>
                        @elseif($movement->type == 'salida')
                            <span class="badge bg-danger">
                                <i class="fas fa-arrow-up"></i> Salida
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-adjust"></i> Ajuste
                            </span>
                        @endif
                    </td>
                    <td class="{{ $movement->type == 'entrada' ? 'text-success' : 'text-danger' }} fw-bold">
                        {{ number_format($movement->quantity, 2) }}
                    </td>
                    <td>{{ number_format($movement->stock_before, 2) }}</td>
                    <td>{{ number_format($movement->stock_after, 2) }}</td>
                    <td>
                        @if($movement->reference_type == 'invoice')
                            <span class="badge bg-info">
                                <i class="fas fa-file-invoice"></i> Factura #{{ $movement->reference_id }}
                            </span>
                        @elseif($movement->reference_type == 'adjustment')
                            <span class="badge bg-secondary">
                                <i class="fas fa-edit"></i> Ajuste manual
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">No hay movimientos registrados</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <select id="perPageSelect" class="form-select form-select-sm d-inline-block w-auto">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-muted small"> registros</span>
        </div>
        <div>
            {{ $movements->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
function showDetails(id) {
    // Modal o redirigir a detalles
    window.location.href = `/inventory-movements/${id}`;
}

$('#perPageSelect').on('change', function() {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', $(this).val());
    window.location.href = currentUrl.toString();
});
</script>