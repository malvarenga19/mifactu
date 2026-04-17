@extends('layouts.app')

@section('title', 'Detalles del Producto')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-box text-info"></i> Detalles del Producto
            </h4>
            <div>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}"
                            class="img-fluid rounded shadow" style="max-height: 300px;">
                    @else
                        <div class="bg-light rounded p-5">
                            <i class="fas fa-box fa-5x text-muted"></i>
                            <p class="text-muted mt-2">Sin imagen</p>
                        </div>
                    @endif
                </div>

                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">ID:</th>
                            <td>{{ $product->id }}</td>
                        </tr>
                        <tr>
                            <th>Código:</th>
                            <td><span class="badge bg-secondary">{{ $product->code ?? 'Sin código' }}</span></td>
                        </tr>
                        <tr>
                            <th>Nombre:</th>
                            <td>
                                <h5 class="mb-0">{{ $product->name }}</h5>
                            </td>
                        </tr>
                        <tr>
                            <th>Descripción:</th>
                            <td>{{ $product->description ?? 'Sin descripción' }}</td>
                        </tr>
                        <tr>
                            <th>Categoría:</th>
                            <td>{{ $product->category?->name ?? 'Sin categoría' }}</td>
                        </tr>
                        <tr>
                            <th>Proveedor:</th>
                            <td>{{ $product->supplier?->name ?? 'Sin proveedor' }}</td>
                        </tr>

                        <tr>
                            <th>Códigos Equivalentes:</th>
                            <td>
                                @if($product->equivalents->count())
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($product->equivalents as $equivalent)
                                            <span class="badge bg-info text-dark p-2">
                                                <i class="fas fa-exchange-alt me-1"></i>
                                                {{ $equivalent->equivalent_code }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">Sin códigos equivalentes registrados</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ubicación:</th>
                            <td>{{ $product->location ?? 'No especificada' }}</td>
                        </tr>
                        <tr>
                            <th>Precio Costo:</th>
                            <td class="text-danger">$ {{ number_format($product->cost_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Precio Venta:</th>
                            <td class="text-success fw-bold">$ {{ number_format($product->sale_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Stock Actual:</th>
                            <td>
                                @if($product->stock <= 0)
                                    <span class="badge bg-danger fs-6">Agotado</span>
                                @elseif($product->stock <= $product->min_stock)
                                    <span class="badge bg-warning fs-6">{{ $product->stock }} unidades</span>
                                @else
                                    <span class="badge bg-success fs-6">{{ $product->stock }} unidades</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Stock Mínimo:</th>
                            <td>{{ $product->min_stock ?? 0 }} unidades</td>
                        </tr>
                        <tr>
                            <th>Fecha Creación:</th>
                            <td>{{ $product->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Última Actualización:</th>
                            <td>{{ $product->updated_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection