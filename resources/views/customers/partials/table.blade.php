{{-- resources/views/customers/partials/table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="50">ID</th>
                <th>Nombre/Empresa</th>
                <th>Documento</th>
                <th>NRC</th>
                <th>Actividad Económica</th>
                <th>Retiene IVA</th>
                <th>Contacto</th>
                <th>Ubicación</th>
                <th width="80">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>
                        <strong>{{ $customer->name }}</strong>
                        @if($customer->company_name)
                            <br>
                            <small class="text-muted">{{ $customer->company_name }}</small>
                        @endif
                    </td>
                    <td>
                        @if($customer->document)
                            <span class="badge bg-secondary">
                                @switch($customer->document)
                                    @case('13') DUI @break
                                    @case('36') NIT @break
                                    @case('03') Pasaporte @break
                                    @case('02') Carnet Residente @break
                                    @default Otro
                                @endswitch
                            </span>
                            <br>
                            <small class="text-muted">{{ $customer->document_number ?? 'Sin número' }}</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $customer->nrc ?? '—' }}</td>
                    <td>
                        @if($customer->economicActivity)
                            <span class="badge bg-info text-dark" style="white-space: normal; word-break: break-word;">
                                {{ Str::limit($customer->economicActivity->description, 50) }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($customer->retains_iva)
                            <span class="badge bg-success">Sí</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </td>
                    <td>
                        @if($customer->email)
                            <i class="fas fa-envelope text-muted small"></i> {{ $customer->email }}<br>
                        @endif
                        @if($customer->phone)
                            <i class="fas fa-phone text-muted small"></i> {{ $customer->phone }}
                        @endif
                        @if(!$customer->email && !$customer->phone)
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($customer->municipality)
                            <small>
                                {{ $customer->municipality->name ?? '' }}
                                @if($customer->municipality?->department)
                                    , {{ $customer->municipality->department->name }}
                                @endif
                                @if($customer->country && $customer->country->name != 'El Salvador')
                                    , {{ $customer->country->name }}
                                @endif
                            </small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                        @if($customer->address)
                            <br>
                            <small class="text-muted">{{ Str::limit($customer->address, 40) }}</small>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
    <a href="{{ route('customers.show', $customer) }}" 
       class="btn btn-info" 
       title="Ver detalles">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('customers.edit', $customer) }}" 
       class="btn btn-warning" 
       title="Editar">
        <i class="fas fa-edit"></i>
    </a>
    <button type="button" 
            class="btn btn-danger" 
            title="Eliminar"
            onclick="confirmDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}')">
        <i class="fas fa-trash"></i>
    </button>
</div>
                        
                        <form id="delete-form-{{ $customer->id }}" 
                              action="{{ route('customers.destroy', $customer) }}" 
                              method="POST" 
                              style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">No se encontraron clientes</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <label class="text-muted small">Mostrar</label>
            <select id="perPageSelect" class="form-select form-select-sm d-inline-block w-auto">
                <option value="10" {{ $customers->perPage() == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $customers->perPage() == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $customers->perPage() == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $customers->perPage() == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="text-muted small">registros por página</span>
        </div>
        <div>
            {{ $customers->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    if (confirm(`¿Estás seguro de eliminar el cliente "${name}"?\n\nEsta acción eliminará todos los datos asociados.`)) {
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