@extends('layouts.app')

@section('title', 'Listado de Proveedores')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-truck text-primary"></i> Proveedores
            </h4>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Proveedor
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive-custom">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Actividad</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th>Departamento</th>
                            <th>Municipio</th>
                            <th>País</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->id }}</td>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->document_number ?? 'N/A' }}</td>
                                <td>{{ $supplier->economicActivity?->description ?? 'N/A' }}</td>
                                <td>{{ $supplier->email ?? 'N/A' }}</td>
                                <td>{{ $supplier->phone ?? 'N/A' }}</td>
                                <td>{{ $supplier->address ?? 'N/A' }}</td>
                                <td>{{ $supplier->municipality?->department?->name ?? 'N/A' }}</td>
                                <td>{{ $supplier->municipality?->name ?? 'N/A' }}</td>
                                <td>{{ $supplier->country?->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline"> --}}
                                        {{-- @csrf --}}
                                        {{-- @method('DELETE') --}}
                                        {{-- <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar proveedor?')"> --}}
                                            {{-- <i class="fas fa-trash"></i> --}}
                                        {{-- </button> --}}
                                    {{-- </form> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No hay proveedores registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection