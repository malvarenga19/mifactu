@extends('layouts.app')

@section('title', 'Detalle de factura')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">
            Factura #{{ $invoice->correlative }}
        </h1>
        <p class="page-subtitle">
            Código: {{ $invoice->generation_code }}
        </p>
    </div>

<div class="d-flex gap-2">
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary">
        <i class="fas fa-pen me-1"></i> Editar
    </a>
</div>


</div>

<div class="row g-4">

{{-- INFO GENERAL --}}
<div class="col-12 col-lg-8">

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-alt me-2"></i> Información general
        </div>
        <div class="card-body">

            <div class="row g-3">

                <div class="col-6 col-md-4">
                    <label class="form-label">Cliente</label>
                    <div>{{ $invoice->customer->name }}</div>
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label">Fecha</label>
                    <div>{{ $invoice->issue_date->format('d/m/Y') }}</div>
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label">Vencimiento</label>
                    <div>
                        {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
                    </div>
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label">Forma de pago</label>
                    <div>{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</div>
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label">Estado</label>
                    <div>
                        <span class="badge-status {{ $invoice->status }}">
                            {{ strtoupper($invoice->status) }}
                        </span>
                    </div>
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label">Estado MH</label>
                    <div>
                        <span class="badge-status {{ $invoice->status_mh }}">
                            {{ strtoupper($invoice->status_mh) }}
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- ITEMS --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-boxes-stacked me-2"></i> Detalle
        </div>

        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th class="text-end">Cant.</th>
                        <th class="text-end">P. Unit</th>
                        <th class="text-center">Exento</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">
                                @if($item->exento)
                                    <i class="fas fa-check text-success"></i>
                                @endif
                            </td>
                            <td class="text-end">
                                ${{ number_format($item->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Sin items
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- NOTA --}}
    @if($invoice->note)
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-note-sticky me-2"></i> Nota
        </div>
        <div class="card-body">
            {{ $invoice->note }}
        </div>
    </div>
    @endif

</div>

{{-- RESUMEN --}}
<div class="col-12 col-lg-4">

    <div class="summary-card">

        <div class="summary-title">
            <i class="fas fa-calculator me-2"></i> Resumen
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span>Exento</span>
            <strong>${{ number_format($invoice->monto_exento, 2) }}</strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span>Gravado</span>
            <strong>${{ number_format($invoice->monto_gravado, 2) }}</strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span>IVA</span>
            <strong>${{ number_format($invoice->monto_iva, 2) }}</strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span>IVA retenido</span>
            <strong>${{ number_format($invoice->iva_retenido, 2) }}</strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span>ISR retenido</span>
            <strong>${{ number_format($invoice->isr_retenido, 2) }}</strong>
        </div>

        <hr>

        <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <strong>${{ number_format($invoice->subtotal, 2) }}</strong>
        </div>

        <div class="d-flex justify-content-between fs-5">
            <span>Total</span>
            <strong>${{ number_format($invoice->total_amount, 2) }}</strong>
        </div>

    </div>

</div>

</div>

@endsection
