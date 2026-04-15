@extends('layouts.app')

@section('title', 'Editar Categoría')

@section('content')
<div class="card shadow">
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-edit text-warning"></i> Editar Categoría
        </h4>
    </div>
    <div class="card-body">
        <form action="{{ route('categories.update', $category) }}" method="POST" id="categoryForm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre *</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $category->name) }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ej: Electrónicos, Ropa, Alimentos, etc.</small>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="4" 
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Descripción opcional de la categoría...">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between gap-2 mt-3">
                <div>
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Categoría
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Validación antes de enviar
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const nameInput = document.getElementById('name');
        if (!nameInput.value.trim()) {
            e.preventDefault();
            alert('El nombre de la categoría es requerido');
            nameInput.focus();
        }
    });
</script>
@endpush