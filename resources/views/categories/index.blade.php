@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-tags text-primary"></i> Categorías
            </h4>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Categoría
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Fecha Creación</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>
                                    <i class="fas fa-tag text-primary"></i>
                                    {{ $category->name }}
                                </td>
                                <td>{{ $category->description ?? '—' }}</td>
                                <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-warning"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $category->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Modal de confirmación para eliminar -->
                                    <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar eliminación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Está seguro que desea eliminar la categoría
                                                    <strong>{{ $category->name }}</strong>?
                                                    <br>
                                                    <small class="text-danger">Esta acción no se puede deshacer.</small>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                                        class="d-inline">
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
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">No hay categorías registradas</p>
                                    <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Crear primera categoría
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <small class="text-muted">
                        Mostrando {{ $categories->firstItem() ?? 0 }} a {{ $categories->lastItem() ?? 0 }}
                        de {{ $categories->total() }} categorías
                    </small>
                </div>
                <div>
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-cerrar alertas después de 5 segundos
        setTimeout(function () {
            $('.alert').alert('close');
        }, 5000);
    </script>
@endpush