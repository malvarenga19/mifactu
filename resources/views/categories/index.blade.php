@extends('layouts.app')

@section('title', 'Categorías')
@section('breadcrumb')
    Inventario / <strong>Categorías</strong>
@endsection

@section('topbar-actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary">+ Nueva categoría</a>
@endsection

@section('content')
<div class="card">
    {{-- Filtros --}}
    <form method="GET" action="{{ route('categories.index') }}" style="margin-bottom:1.4rem;">
        <div class="form-row" style="grid-template-columns:1fr auto auto;">
            <div class="form-group" style="margin:0">
                <input type="text" name="search" placeholder="Buscar categoría…" value="{{ request('search') }}">
            </div>
            <div style="display:flex;gap:0.4rem;align-items:center;">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm">✕</a>
            </div>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Fecha creación</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td style="font-family:var(--mono);font-size:0.8rem;color:var(--muted)">{{ $category->id }}</td>
                    <td style="font-weight:500">{{ $category->name }}</td>
                    <td style="color:var(--muted)">{{ $category->description ?? '—' }}</td>
                    <td style="font-family:var(--mono);font-size:0.75rem;color:var(--muted)">
                        {{ $category->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <div style="display:flex;gap:0.3rem">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-secondary btn-sm">Editar</a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $category->id }}, '{{ $category->name }}')">Eliminar</button>
                        </div>

                        {{-- Modal oculto para eliminar --}}
                        <div id="deleteModal{{ $category->id }}" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); align-items:center; justify-content:center; z-index:1000;">
                            <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); max-width:400px; width:90%; padding:1.5rem;">
                                <h4 style="margin-bottom:1rem; font-family:var(--mono);">Confirmar eliminación</h4>
                                <p style="margin-bottom:1.5rem">¿Eliminar la categoría <strong>{{ $category->name }}</strong>?<br><small style="color:var(--danger)">Esta acción no se puede deshacer.</small></p>
                                <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal({{ $category->id }})">Cancelar</button>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" style="margin:0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:var(--muted);padding:2.5rem">
                        No se encontraron categorías.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1rem;">
        <span style="font-size:0.8rem;color:var(--muted)">
            {{ $categories->firstItem() }}–{{ $categories->lastItem() }} de {{ $categories->total() }} registros
        </span>
        <div class="pagination">
            @if($categories->onFirstPage())
                <span>‹</span>
            @else
                <a href="{{ $categories->previousPageUrl() . (request('search') ? '&search='.request('search') : '') }}">‹</a>
            @endif

            @foreach($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                @if($page == $categories->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url . (request('search') ? '&search='.request('search') : '') }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($categories->hasMorePages())
                <a href="{{ $categories->nextPageUrl() . (request('search') ? '&search='.request('search') : '') }}">›</a>
            @else
                <span>›</span>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteModal' + id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById('deleteModal' + id).style.display = 'none';
}
// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    if (event.target.classList && event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
@endpush