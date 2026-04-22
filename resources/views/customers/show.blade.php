@extends('layouts.app')

@section('title', 'Detalles del Cliente')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-user-circle text-info"></i> Detalles del Cliente
            </h4>
            <div>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="card bg-light mb-3">
                        <div class="card-body text-center">
                            <i class="fas fa-user-circle fa-5x text-primary mb-3"></i>
                            <h3 class="mb-1">{{ $customer->name }}</h3>
                            @if($customer->company_name)
                                <p class="text-muted">
                                    <i class="fas fa-building"></i> {{ $customer->company_name }}
                                </p>
                            @endif
                            
                            <hr>
                            
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <small class="text-muted d-block">Retiene IVA</small>
                                        @if($customer->retains_iva)
                                            <span class="badge bg-success fs-6">Sí</span>
                                        @else
                                            <span class="badge bg-secondary fs-6">No</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <small class="text-muted d-block">ID Cliente</small>
                                        <strong class="fs-5">#{{ $customer->id }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <table class="table table-borderless">
                        <!-- Información de Documento -->
                        <tr class="table-light">
                            <th colspan="2" class="py-2">
                                <i class="fas fa-id-card"></i> Información Documental
                            </th>
                        </tr>
                        <tr>
                            <th width="150">Tipo de Documento:</th>
                            <td>
                                @if($customer->document)
                                    <span class="badge bg-secondary">
                                        @switch($customer->document)
                                            @case('13') DUI (Documento Único de Identidad) @break
                                            @case('36') NIT (Número de Identificación Tributaria) @break
                                            @case('03') Pasaporte @break
                                            @case('02') Carnet de Residente @break
                                            @default Otro
                                        @endswitch
                                    </span>
                                @else
                                    <span class="text-muted">No especificado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Número de Documento:</th>
                            <td><strong>{{ $customer->document_number ?? '—' }}</strong></td>
                        </tr>
                        <tr>
                            <th>NRC:</th>
                            <td>
                                @if($customer->nrc)
                                    <span class="badge bg-info text-dark">{{ $customer->nrc }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Actividad Económica:</th>
                            <td>
                                @if($customer->economicActivity)
                                    <div>
                                        <span class="badge bg-primary mb-1">Código: {{ $customer->economicActivity->code ?? 'N/A' }}</span>
                                        <p class="mt-2 mb-0">{{ $customer->economicActivity->description }}</p>
                                    </div>
                                @else
                                    <span class="text-muted">No especificada</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Información de Contacto -->
                        <tr class="table-light">
                            <th colspan="2" class="py-2">
                                <i class="fas fa-address-card"></i> Información de Contacto
                            </th>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>
                                @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}" class="text-decoration-none">
                                        <i class="fas fa-envelope"></i> {{ $customer->email }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td>
                                @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" class="text-decoration-none">
                                        <i class="fas fa-phone"></i> {{ $customer->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dirección:</th>
                            <td>
                                @if($customer->address)
                                    <i class="fas fa-map-marker-alt text-danger"></i> {{ $customer->address }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Información de Ubicación -->
                        <tr class="table-light">
                            <th colspan="2" class="py-2">
                                <i class="fas fa-map-marked-alt"></i> Ubicación Geográfica
                            </th>
                        </tr>
                        <tr>
                            <th>País:</th>
                            <td>{{ $customer->country?->name ?? 'No especificado' }}</td>
                        </tr>
                        <tr>
                            <th>Departamento:</th>
                            <td>{{ $customer->municipality?->department?->name ?? 'No especificado' }}</td>
                        </tr>
                        <tr>
                            <th>Municipio:</th>
                            <td>{{ $customer->municipality?->name ?? 'No especificado' }}</td>
                        </tr>

                        <!-- Auditoría -->
                        <tr class="table-light">
                            <th colspan="2" class="py-2">
                                <i class="fas fa-history"></i> Auditoría
                            </th>
                        </tr>
                        <tr>
                            <th>Fecha Creación:</th>
                            <td>{{ $customer->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Última Actualización:</th>
                            <td>{{ $customer->updated_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection