@extends('layouts.app')

@section('title', 'Ver Producto: ' . $product->name)
@section('breadcrumb', 'Productos / <strong>' . e($product->name) . '</strong>')

@section('topbar-actions')
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">✏️ Editar</a>
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">← Volver</a>
    </div>
@endsection

@push('styles')
<style>
    .detail-card {
        background: var(--surface1);
        border-radius: var(--radius);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .detail-item {
        border-bottom: 1px solid var(--border);
        padding: 0.75rem 0;
    }
    .detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--muted);
        margin-bottom: 0.25rem;
    }
    .detail-value {
        font-size: 1rem;
        font-weight: 500;
        color: var(--text);
    }
    .badge-stock {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-low {
        background: rgba(255, 92, 92, 0.15);
        color: var(--danger);
    }
    .badge-normal {
        background: rgba(0, 200, 100, 0.15);
        color: var(--success);
    }
    .product-image {
        max-width: 100%;
        max-height: 200px;
        border-radius: var(--radius);
        object-fit: contain;
    }
</style>
@endpush

@section('content')
<div style="display:grid; grid-template-columns: 1fr 300px; gap:1.5rem; align-items:start">

    {{-- Columna principal --}}
    <div>
        {{-- Información general --}}
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:1rem">◈ Información general</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Nombre</div>
                    <div class="detail-value">{{ $product->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Código</div>
                    <div class="detail-value">{{ $product->code ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Categoría</div>
                    <div class="detail-value">{{ $product->category?->name ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Proveedor</div>
                    <div class="detail-value">{{ $product->supplier?->name ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Ubicación</div>
                    <div class="detail-value">{{ $product->location ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Descripción</div>
                    <div class="detail-value">{{ $product->description ?: '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Precios y stock --}}
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:1rem">◉ Precios y stock</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Precio de costo</div>
                    <div class="detail-value">${{ number_format($product->cost_price ?? 0, 2) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Precio de venta</div>
                    <div class="detail-value">${{ number_format($product->sale_price, 2) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Stock actual</div>
                    <div class="detail-value">
                        {{ number_format($product->stock, 2) }}
                        @php
                            $minStock = floatval($product->min_stock ?? 0);
                            $stock = floatval($product->stock);
                        @endphp
                        @if($minStock > 0 && $stock <= $minStock)
                            <span class="badge-stock badge-low" style="margin-left:0.5rem;">⚠️ Stock bajo</span>
                        @elseif($stock > 0)
                            <span class="badge-stock badge-normal" style="margin-left:0.5rem;">✓ Disponible</span>
                        @else
                            <span class="badge-stock badge-low" style="margin-left:0.5rem;">✗ Agotado</span>
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Stock mínimo</div>
                    <div class="detail-value">{{ number_format($minStock, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Códigos equivalentes --}}
        @if($product->equivalents->count() > 0)
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:0.75rem">◈ Códigos equivalentes</div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                @foreach($product->equivalents as $code)
                    <span style="background: var(--surface2); padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem;">
                        {{ $code->equivalent_code }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Panel lateral --}}
    <div style="position:sticky; top:1rem;">
        <div class="detail-card" style="text-align:center;">
            <div class="card-title" style="margin-bottom:1rem">◎ Imagen</div>
            @if($product->image_path && Storage::disk('public')->exists($product->image_path))
                <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="product-image">
            @else
                <div style="background: var(--surface2); border-radius: var(--radius); padding: 2rem; color: var(--muted);">
                    <div style="font-size: 3rem;">📷</div>
                    <div style="margin-top: 0.5rem;">Sin imagen</div>
                </div>
            @endif
        </div>

        <div class="detail-card">
            <div class="card-title" style="margin-bottom:0.75rem">◎ Metadatos</div>
            <div class="detail-item">
                <div class="detail-label">Creado</div>
                <div class="detail-value">{{ $product->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Última actualización</div>
                <div class="detail-value">{{ $product->updated_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection