@extends('layouts.app')

@section('title', 'Movimientos de Inventario')
@section('breadcrumb')
    Inventario / <strong>Movimientos</strong>
@endsection

@section('topbar-actions')
    <a href="{{ route('inventory_movements.create') }}" class="btn btn-primary">+ Nuevo movimiento</a>
@endsection

@section('content')
<div class="card">
    {{-- Filtros --}}
    <form method="GET" action="{{ route('inventory_movements.index') }}" id="filterForm" style="margin-bottom:1.4rem;">
        <div class="form-row" style="grid-template-columns:1fr 160px 160px 160px auto;">
            <div class="form-group" style="margin:0">
                <input type="text" name="search" placeholder="Buscar producto…" value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin:0">
                <select name="type">
                    <option value="">Todos los tipos</option>
                    <option value="entrada" @selected(request('type')=='entrada')>Entrada</option>
                    <option value="salida" @selected(request('type')=='salida')>Salida</option>
                    <option value="ajuste" @selected(request('type')=='ajuste')>Ajuste</option>
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <input type="date" name="date_from" value="{{ request('date_from') }}" title="Desde">
            </div>
            <div class="form-group" style="margin:0">
                <input type="date" name="date_to" value="{{ request('date_to') }}" title="Hasta">
            </div>
            <div style="display:flex;gap:0.4rem;align-items:center;">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="{{ route('inventory_movements.index') }}" class="btn btn-secondary btn-sm">✕</a>
            </div>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha/Hora</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th style="text-align:right">Cantidad</th>
                    <th style="text-align:right">Stock anterior</th>
                    <th style="text-align:right">Stock nuevo</th>
                    <th>Referencia</th>
                </tr>
            </thead>
            <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td style="font-family:var(--mono);font-size:0.75rem;color:var(--muted)">{{ $movement->id }}</td>
                    <td style="font-family:var(--mono);font-size:0.7rem">
                        {{ $movement->created_at->format('d/m/Y') }}<br>
                        <span style="color:var(--muted)">{{ $movement->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        <strong>{{ $movement->product->name ?? 'Producto eliminado' }}</strong>
                        @if($movement->product && $movement->product->code)
                            <div style="font-size:0.7rem;color:var(--muted)">Código: {{ $movement->product->code }}</div>
                        @endif
                    </td>
                    <td>
                        @php
                            $typeClasses = ['entrada' => 'success', 'salida' => 'danger', 'ajuste' => 'warn'];
                            $typeIcons = ['entrada' => '↓', 'salida' => '↑', 'ajuste' => '◉'];
                        @endphp
                        <span class="badge badge-{{ $typeClasses[$movement->type] ?? 'draft' }}">
                            {{ $typeIcons[$movement->type] ?? '' }} {{ ucfirst($movement->type) }}
                        </span>
                    </td>
                    <td style="text-align:right;font-family:var(--mono);font-weight:700;{{ $movement->type == 'entrada' ? 'color:var(--success)' : ($movement->type == 'salida' ? 'color:var(--danger)' : 'color:var(--warn)') }}">
                        {{ number_format($movement->quantity, 2) }}
                    </td>
                    <td style="text-align:right;font-family:var(--mono)">{{ number_format($movement->stock_before, 2) }}</td>
                    <td style="text-align:right;font-family:var(--mono)">{{ number_format($movement->stock_after, 2) }}</td>
                    <td style="color:var(--muted);font-size:0.8rem">{{ $movement->note ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--muted);padding:2.5rem">
                        No se encontraron movimientos de inventario.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1rem;">
        <span style="font-size:0.8rem;color:var(--muted)">
            {{ $movements->firstItem() }}–{{ $movements->lastItem() }} de {{ $movements->total() }} registros
        </span>
        <div class="pagination">
            @if($movements->onFirstPage())
                <span>‹</span>
            @else
                <a href="{{ $movements->previousPageUrl() . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}">‹</a>
            @endif

            @foreach($movements->getUrlRange(1, $movements->lastPage()) as $page => $url)
                @if($page == $movements->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url . (request()->getQueryString() ? (strpos($url, '?') !== false ? '&' : '?') . request()->getQueryString() : '') }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($movements->hasMorePages())
                <a href="{{ $movements->nextPageUrl() . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}">›</a>
            @else
                <span>›</span>
            @endif
        </div>
    </div>
</div>
@endsection