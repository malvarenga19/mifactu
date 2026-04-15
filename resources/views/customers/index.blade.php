@extends('layouts.app')

@section('title', 'Listado de Clientes')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-users text-primary"></i> Clientes
            </h4>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Empresa</th>
                            <th>Documento</th>
                            <th>NRC</th>
                            <th>Actividad</th>
                            <th>Retiene IVA</th>
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
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->company_name ?? 'N/A' }}</td>
                                <td>
                                    {{ $customer->document ?? 'N/A' }}<br>
                                    <small>{{ $customer->document_number ?? '' }}</small>
                                </td>
                                <td>{{ $customer->nrc ?? 'N/A' }}</td>
                                <td>{{ $customer->economicActivity?->description ?? 'N/A' }}</td>
                                <td>
                                    @if($customer->retains_iva)
                                        <span class="badge bg-success">Sí</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $customer->email ?? 'N/A' }}</td>
                                <td>{{ $customer->phone ?? 'N/A' }}</td>
                                <td>{{ $customer->address ?? 'N/A' }}</td>
                                <td>{{ $customer->municipality?->department?->name ?? 'N/A' }}</td>
                                <td>{{ $customer->municipality?->name ?? 'N/A' }}</td>
                                <td>{{ $customer->country?->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center">No hay clientes registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación - Agrega esto -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <small class="text-muted">
                        Mostrando {{ $customers->firstItem() ?? 0 }} a {{ $customers->lastItem() ?? 0 }}
                        de {{ $customers->total() }} clientes
                    </small>
                </div>
                <div>
                    {{ $customers->links() }}
                </div>
            </div>
            <!-- Fin paginación -->

        </div>
    </div>
@endsection