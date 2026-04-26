@extends('layouts.app')

@section('title', 'Facturas')
@section('breadcrumb')
    Facturación / <strong>Listado</strong>
@endsection

@section('topbar-actions')
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">+ Nueva factura</a>
@endsection

@section('content')

<div class="card">
    {{-- Filtros --}}
    <form method="GET" action="{{ route('invoices.index') }}" style="margin-bottom:1.4rem;">
        <div class="form-row" style="grid-template-columns:1fr 1fr 160px 160px 140px 140px auto;">
            <div class="form-group" style="margin:0">
                <input type="text" name="search" placeholder="Correlativo o cliente…" value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin:0">
                <select name="invoice_type_id">
                    <option value="">Todos los tipos</option>
                    @foreach($invoiceTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('invoice_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <select name="status">
                    <option value="">Estado</option>
                    <option value="draft"     @selected(request('status')=='draft')>Borrador</option>
                    <option value="issued"    @selected(request('status')=='issued')>Emitida</option>
                    <option value="cancelled" @selected(request('status')=='cancelled')>Anulada</option>
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <select name="payment_status">
                    <option value="">Pago</option>
                    <option value="pending" @selected(request('payment_status')=='pending')>Pendiente</option>
                    <option value="paid"    @selected(request('payment_status')=='paid')>Pagada</option>
                    <option value="overdue" @selected(request('payment_status')=='overdue')>Vencida</option>
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
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm">✕</a>
            </div>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Correlativo</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Método pago</th>
                    <th style="text-align:right">Total</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>MH</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td style="font-family:var(--mono);font-size:0.8rem;color:var(--accent)">{{ $inv->correlative }}</td>
                    <td style="font-size:0.82rem;color:var(--muted)">{{ $inv->invoiceType->name ?? '—' }}</td>
                    <td>
                        <div style="font-weight:500">{{ $inv->customer->name }}</div>
                        @if($inv->customer->company_name)
                            <div style="font-size:0.78rem;color:var(--muted)">{{ $inv->customer->company_name }}</div>
                        @endif
                    </td>
                    <td style="font-family:var(--mono);font-size:0.8rem">{{ \Carbon\Carbon::parse($inv->issue_date)->format('d/m/Y') }}</td>
                    <td style="font-size:0.82rem">
                        @php $pm = ['cash'=>'Efectivo','credit_card'=>'Tarjeta','bank_transfer'=>'Transferencia','credit'=>'Crédito']; @endphp
                        {{ $pm[$inv->payment_method] ?? $inv->payment_method }}
                    </td>
                    <td style="text-align:right;font-family:var(--mono);font-weight:700">
                        ${{ number_format($inv->total_amount, 2) }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $inv->status }}">
                            {{ ['draft'=>'Borrador','issued'=>'Emitida','cancelled'=>'Anulada'][$inv->status] ?? $inv->status }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $inv->payment_status }}">
                            {{ ['pending'=>'Pendiente','paid'=>'Pagada','overdue'=>'Vencida'][$inv->payment_status] ?? $inv->payment_status }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $inv->status_mh === 'received' ? 'issued' : ($inv->status_mh === 'rejected' ? 'cancelled' : 'draft') }}"
                              style="font-size:0.65rem">
                            {{ strtoupper($inv->status_mh) }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:0.3rem">
                            <a href="{{ route('invoices.show', $inv) }}" class="btn btn-secondary btn-sm">Ver</a>
                            @if($inv->status === 'draft')
                                <a href="{{ route('invoices.edit', $inv) }}" class="btn btn-secondary btn-sm">Editar</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;color:var(--muted);padding:2.5rem">
                        No se encontraron facturas.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1rem;">
        <span style="font-size:0.8rem;color:var(--muted)">
            {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} de {{ $invoices->total() }} registros
        </span>
        <div class="pagination">
            @if($invoices->onFirstPage())
                <span>‹</span>
            @else
                <a href="{{ $invoices->previousPageUrl() }}">‹</a>
            @endif

            @foreach($invoices->getUrlRange(1, $invoices->lastPage()) as $page => $url)
                @if($page == $invoices->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($invoices->hasMorePages())
                <a href="{{ $invoices->nextPageUrl() }}">›</a>
            @else
                <span>›</span>
            @endif
        </div>
    </div>
</div>
@endsection