@extends('layouts.app')

@section('title', 'Facturas')

@push('styles')
<style>
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-family: var(--mono);
        font-size: 10px;
        font-weight: 500;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 3px 9px;
        border-radius: 2px;
    }
    .status-badge.draft      { background: rgba(140,136,128,.12); color: var(--muted); }
    .status-badge.issued     { background: rgba(45,106,79,.12);   color: var(--success); }
    .status-badge.cancelled  { background: rgba(155,53,53,.12);   color: var(--danger); }

    .mh-badge.draft      { background: rgba(140,136,128,.12); color: var(--muted); }
    .mh-badge.received   { background: rgba(45,106,79,.12);   color: var(--success); }
    .mh-badge.rejected   { background: rgba(155,53,53,.12);   color: var(--danger); }
    .mh-badge.cancelled  { background: rgba(155,53,53,.12);   color: var(--danger); }

    .filter-bar {
        background: var(--paper);
        border: 1px solid var(--rule);
        border-radius: var(--radius);
        padding: 18px 20px;
        margin-bottom: 20px;
    }

    .amount-cell {
        font-family: var(--mono);
        font-size: .82rem;
        text-align: right;
        white-space: nowrap;
    }

    .corr-cell {
        font-family: var(--mono);
        font-size: .8rem;
        color: var(--muted);
        white-space: nowrap;
    }

    .table-invoices th {
        font-family: var(--mono);
        font-size: 10px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 500;
        border-bottom: 1px solid var(--rule);
        padding: 10px 14px;
        white-space: nowrap;
    }

    .table-invoices td {
        padding: 11px 14px;
        vertical-align: middle;
        border-bottom: 1px solid rgba(222,218,211,.5);
        font-size: .875rem;
    }

    .table-invoices tbody tr:hover {
        background: rgba(184,146,42,.04);
    }

    .customer-name {
        font-weight: 500;
        color: var(--ink);
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .customer-doc {
        font-size: .75rem;
        color: var(--muted);
        font-family: var(--mono);
    }

    .doc-type-tag {
        display: inline-block;
        font-family: var(--mono);
        font-size: 10px;
        letter-spacing: .04em;
        padding: 2px 7px;
        border-radius: 2px;
        background: var(--accent-lt);
        color: var(--accent-dk);
        font-weight: 500;
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Facturas</h1>
        <p class="page-subtitle">Documentos tributarios emitidos</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nueva Factura
    </a>
</div>

{{-- Filtros --}}
<div class="filter-bar no-print">
    <form method="GET" action="{{ route('invoices.index') }}" id="filterForm">
        <div class="row g-2 align-items-end">

            <div class="col-12 col-md-4">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Correlativo, código, cliente…"
                           value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Tipo DTE</label>
                <select name="invoice_type_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($invoiceTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('invoice_type_id') == $type->id)>
                            {{ $type->code }} — {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="draft"     @selected(request('status') === 'draft')>Borrador</option>
                    <option value="issued"    @selected(request('status') === 'issued')>Emitida</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Anulada</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-12 d-flex gap-2 mt-1">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-xmark me-1"></i>Limpiar
                </a>
            </div>

        </div>
    </form>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-invoices mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Correlativo</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th class="text-end">Total</th>
                        <th>Estado</th>
                        <th>MH</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td class="corr-cell">{{ $invoice->id }}</td>

                        <td>
                            <span class="doc-type-tag">{{ $invoice->invoiceType->code }}</span>
                        </td>

                        <td class="corr-cell">{{ $invoice->correlative }}</td>

                        <td style="white-space:nowrap; font-size:.82rem;">
                            {{ $invoice->issue_date->format('d/m/Y') }}
                        </td>

                        <td>
                            <div class="customer-name">
                                {{ $invoice->customer->company_name ?: $invoice->customer->name }}
                            </div>
                            @if($invoice->customer->document_number)
                            <div class="customer-doc">
                                {{ $invoice->customer->documentTypeName }}: {{ $invoice->customer->document_number }}
                            </div>
                            @endif
                        </td>

                        <td class="amount-cell">
                            ${{ number_format($invoice->total_amount, 2) }}
                        </td>

                        <td>
                            <span class="status-badge {{ $invoice->status }}">
                                @switch($invoice->status)
                                    @case('draft')     <i class="fas fa-pencil"></i> Borrador  @break
                                    @case('issued')    <i class="fas fa-check"></i> Emitida    @break
                                    @case('cancelled') <i class="fas fa-ban"></i> Anulada      @break
                                @endswitch
                            </span>
                        </td>

                        <td>
                            <span class="status-badge mh-badge {{ $invoice->status_mh }}">
                                @switch($invoice->status_mh)
                                    @case('draft')     Pendiente  @break
                                    @case('received')  Recibido   @break
                                    @case('rejected')  Rechazado  @break
                                    @case('cancelled') Anulado    @break
                                @endswitch
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('invoices.show', $invoice) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($invoice->status === 'draft')
                                <form action="{{ route('invoices.destroy', $invoice) }}"
                                      method="POST"
                                      onsubmit="return confirm('¿Eliminar este borrador?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar borrador">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice fa-2x mb-3 d-block opacity-25"></i>
                            No se encontraron facturas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoices->hasPages())
    <div class="card-footer d-flex align-items-center justify-content-between py-2 px-3">
        <small class="text-muted">
            Mostrando {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }}
            de {{ $invoices->total() }} facturas
        </small>
        {{ $invoices->links() }}
    </div>
    @endif
</div>

@endsection