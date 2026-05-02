@extends('layouts.app')

@section('title', 'Factura ' . $invoice->correlative)
@section('breadcrumb', 'Facturas / <strong>' . $invoice->correlative . '</strong>')

@section('topbar-actions')
    <div style="display:flex;gap:0.5rem;align-items:center">
        @if($invoice->status === 'draft')
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-secondary btn-sm">✎ Editar</a>
            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                  onsubmit="return confirm('¿Eliminar esta factura?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">✕ Eliminar</button>
            </form>
        @endif
        @if($invoice->status !== 'cancelled')
            <button onclick="document.getElementById('cancel-modal').style.display='flex'"
                    class="btn btn-danger btn-sm">⊘ Anular</button>
        @endif
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm">← Volver</a>
    </div>
@endsection

@section('content')

@if($errors->has('stock'))
    <div class="alert alert-error">
        <strong>Stock insuficiente:</strong><br>
        {{ $errors->first('stock') }}
    </div>
@endif

@php
    $statusLabels = ['draft'=>'Borrador','issued'=>'Emitida','cancelled'=>'Anulada'];
    $payLabels    = ['pending'=>'Pendiente','paid'=>'Pagada','overdue'=>'Vencida'];
    $pmLabels     = ['cash'=>'Efectivo','credit_card'=>'Tarjeta de crédito','bank_transfer'=>'Transferencia bancaria','credit'=>'Crédito'];
@endphp

<div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">

    {{-- Columna principal --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem">

        {{-- Encabezado factura --}}
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem">
                <div>
                    <div style="font-family:var(--mono);font-size:0.7rem;color:var(--muted);letter-spacing:.1em;text-transform:uppercase;margin-bottom:.3rem">
                        {{ $invoice->invoiceType->name ?? 'Factura' }}
                    </div>
                    <div style="font-family:var(--mono);font-size:1.8rem;font-weight:700;color:var(--accent)">
                        {{ $invoice->correlative }}
                    </div>
                    @if($invoice->generation_code)
                        <div style="font-size:0.7rem;color:var(--muted);margin-top:.3rem;font-family:var(--mono)">
                            Codigo de generación: {{ $invoice->generation_code }}
                        </div>
                    @endif
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
                    <span class="badge badge-{{ $invoice->status }}">
                        {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                    </span>
                    <span class="badge badge-{{ $invoice->payment_status }}">
                        {{ $payLabels[$invoice->payment_status] ?? $invoice->payment_status }}
                    </span>
                    @if($invoice->mh_stamp)
                        <div style="font-size:0.68rem;color:var(--muted);font-family:var(--mono)">
                            Sello MH: {{ $invoice->mh_stamp }}
                        </div>
                    @endif
                </div>
            </div>

            <hr style="border:none;border-top:1px solid var(--border);margin:1.2rem 0">

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem">
                <div>
                    <div style="font-size:0.7rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem">Fecha emisión</div>
                    <div style="font-family:var(--mono)">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</div>
                </div>
                @if($invoice->due_date)
                <div>
                    <div style="font-size:0.7rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem">Vencimiento</div>
                    <div style="font-family:var(--mono)">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
                </div>
                @endif
                <div>
                    <div style="font-size:0.7rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem">Método de pago</div>
                    <div>{{ $pmLabels[$invoice->payment_method] ?? $invoice->payment_method }}</div>
                </div>
                <div>
                    <div style="font-size:0.7rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem">Estado MH</div>
                    <div style="font-family:var(--mono);font-size:0.82rem">{{ strtoupper($invoice->status_mh) }}</div>
                </div>
            </div>

            @if($invoice->cancellation_reason)
                <div style="margin-top:1rem;padding:.75rem 1rem;background:rgba(255,92,92,.07);border:1px solid rgba(255,92,92,.2);border-radius:var(--radius)">
                    <div style="font-size:0.72rem;color:var(--danger);font-family:var(--mono);margin-bottom:.2rem">MOTIVO DE ANULACIÓN</div>
                    <div style="font-size:0.88rem">{{ $invoice->cancellation_reason }}</div>
                    @if($invoice->cancellation_date)
                        <div style="font-size:0.75rem;color:var(--muted);margin-top:.3rem">
                            {{ \Carbon\Carbon::parse($invoice->cancellation_date)->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Datos del cliente --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">◈ Cliente</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;font-size:0.88rem">
                <div>
                    <div style="font-size:0.7rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem">Nombre</div>
                    <div style="font-weight:500">{{ $invoice->customer->name }}</div>
                    @if($invoice->customer->company_name)
                        <div style="color:var(--muted);font-size:0.82rem">{{ $invoice->customer->company_name }}</div>
                    @endif
                </div>
                @if($invoice->customer->document_number)
                <div>
                    <div style="font-size:0.7rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem">{{ $invoice->customer->document ?? 'DUI/NIT' }}</div>
                    <div style="font-family:var(--mono)">{{ $invoice->customer->document_number }}</div>
                    @if($invoice->customer->nrc)
                        <div style="font-size:0.78rem;color:var(--muted)">NRC: {{ $invoice->customer->nrc }}</div>
                    @endif
                </div>
                @endif
                @if($invoice->customer->email || $invoice->customer->phone)
                <div>
                    <div style="font-size:0.7rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem">Contacto</div>
                    @if($invoice->customer->email)
                        <div>{{ $invoice->customer->email }}</div>
                    @endif
                    @if($invoice->customer->phone)
                        <div style="color:var(--muted)">{{ $invoice->customer->phone }}</div>
                    @endif
                </div>
                @endif
                @if($invoice->customer->address)
                <div>
                    <div style="font-size:0.7rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem">Dirección</div>
                    <div>{{ $invoice->customer->address }}</div>
                    @if($invoice->customer->municipality)
                        <div style="color:var(--muted);font-size:0.82rem">
                            {{ $invoice->customer->municipality->name }}
                            @if($invoice->customer->municipality->department)
                                , {{ $invoice->customer->municipality->department->name }}
                            @endif
                        </div>
                    @endif
                </div>
                @endif
                @if($invoice->customer->retains_iva)
                <div>
                    <span class="badge" style="background:rgba(78,205,196,.12);color:var(--accent2)">Retiene IVA</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Ítems --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">◉ Detalle de productos</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th style="text-align:center">Cant.</th>
                            <th style="text-align:right">P. Unitario</th>
                            <th style="text-align:center">Exento</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($invoice->items as $i => $item)
                        <tr>
                            <td style="color:var(--muted);font-family:var(--mono);font-size:0.78rem">{{ $i+1 }}</td>
                            <td style="font-family:var(--mono);font-size:0.78rem;color:var(--muted)">
                                {{ $item->product->code ?? '—' }}
                            </td>
                            <td>
                                <div style="font-weight:500">{{ $item->description }}</div>
                                @if($item->product && $item->product->name !== $item->description)
                                    <div style="font-size:0.78rem;color:var(--muted)">{{ $item->product->name }}</div>
                                @endif
                            </td>
                            <td style="text-align:center;font-family:var(--mono)">{{ $item->quantity }}</td>
                            <td style="text-align:right;font-family:var(--mono)">${{ number_format($item->unit_price,2) }}</td>
                            <td style="text-align:center">
                                @if($item->exento)
                                    <span class="badge" style="background:rgba(255,165,82,.1);color:var(--warn)">Exento</span>
                                @else
                                    <span style="color:var(--muted);font-size:0.78rem">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;font-family:var(--mono);font-weight:700">${{ number_format($item->total,2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($invoice->note)
                <div style="margin-top:1rem;padding:.75rem;background:var(--surface2);border-radius:var(--radius);font-size:0.85rem;color:var(--muted)">
                    <strong style="color:var(--text)">Nota:</strong> {{ $invoice->note }}
                </div>
            @endif
        </div>
    </div>

    {{-- Columna lateral: totales --}}
    <div style="display:flex;flex-direction:column;gap:1rem;position:sticky;top:1rem">
        <div class="card">
            <div class="card-title" style="margin-bottom:1rem">◎ Resumen</div>
            <div style="display:flex;flex-direction:column;gap:.6rem;font-size:0.88rem">
                @if($invoice->monto_exento > 0)
                <div style="display:flex;justify-content:space-between">
                    <span style="color:var(--muted)">Monto exento</span>
                    <span style="font-family:var(--mono)">${{ number_format($invoice->monto_exento,2) }}</span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between">
                    <span style="color:var(--muted)">Monto gravado</span>
                    <span style="font-family:var(--mono)">${{ number_format($invoice->monto_gravado,2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between">
                    <span style="color:var(--muted)">IVA (13%)</span>
                    <span style="font-family:var(--mono)">${{ number_format($invoice->monto_iva,2) }}</span>
                </div>
                @if($invoice->iva_retenido > 0)
                <div style="display:flex;justify-content:space-between">
                    <span style="color:var(--warn)">IVA retenido</span>
                    <span style="font-family:var(--mono);color:var(--warn)">-${{ number_format($invoice->iva_retenido,2) }}</span>
                </div>
                @endif
                @if($invoice->isr_retenido > 0)
                <div style="display:flex;justify-content:space-between">
                    <span style="color:var(--warn)">ISR retenido</span>
                    <span style="font-family:var(--mono);color:var(--warn)">-${{ number_format($invoice->isr_retenido,2) }}</span>
                </div>
                @endif
                <hr style="border:none;border-top:1px solid var(--border);margin:.3rem 0">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:0.75rem;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.08em">Total</span>
                    <span style="font-family:var(--mono);font-size:1.4rem;font-weight:700;color:var(--accent)">
                        ${{ number_format($invoice->total_amount,2) }}
                    </span>
                </div>
            </div>
        </div>

        @if($invoice->mh_stamp || $invoice->cancellation_mh_stamp)
        <div class="card" style="font-size:0.78rem">
            <div class="card-title" style="margin-bottom:.8rem">◇ Sellos MH</div>
            @if($invoice->mh_stamp)
                <div style="margin-bottom:.5rem">
                    <div style="color:var(--muted);margin-bottom:.2rem">Sello emisión</div>
                    <div style="font-family:var(--mono);word-break:break-all;color:var(--accent2)">{{ $invoice->mh_stamp }}</div>
                </div>
            @endif
            @if($invoice->cancellation_mh_stamp)
                <div>
                    <div style="color:var(--muted);margin-bottom:.2rem">Sello anulación</div>
                    <div style="font-family:var(--mono);word-break:break-all;color:var(--danger)">{{ $invoice->cancellation_mh_stamp }}</div>
                </div>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- Modal anulación --}}
<div id="cancel-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:999;align-items:center;justify-content:center">
    <div class="card" style="width:460px;max-width:95vw">
        <div class="card-header">
            <span class="card-title" style="color:var(--danger)">⊘ Anular factura</span>
            <button onclick="document.getElementById('cancel-modal').style.display='none'"
                    style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.2rem">✕</button>
        </div>
        <form method="POST" action="{{ route('invoices.cancel', $invoice) }}">
            @csrf @method('PATCH')
            <div class="form-group">
                <label>Motivo de anulación *</label>
                <textarea name="cancellation_reason" rows="3" required placeholder="Describe el motivo…"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.5rem">
                <button type="button" onclick="document.getElementById('cancel-modal').style.display='none'"
                        class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-danger">Confirmar anulación</button>
            </div>
        </form>
    </div>
</div>
@endsection