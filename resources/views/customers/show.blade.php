@extends('layouts.app')

@section('title', 'Ver Cliente: ' . $customer->name)
@section('breadcrumb', 'Clientes / <strong>' . e($customer->name) . '</strong>')

@section('topbar-actions')
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm">✏️ Editar</a>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">← Volver</a>
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
    .badge-iva {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(255, 193, 7, 0.15);
        color: var(--warn);
    }
</style>
@endpush

@section('content')
<div style="display:grid; grid-template-columns: 1fr 300px; gap:1.5rem; align-items:start">

    {{-- Columna principal --}}
    <div>
        {{-- Datos personales / empresa --}}
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:1rem">◈ Datos del cliente</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Nombre completo</div>
                    <div class="detail-value">{{ $customer->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Nombre de empresa</div>
                    <div class="detail-value">{{ $customer->company_name ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tipo de documento</div>
                    <div class="detail-value">
                        @switch($customer->document)
                            @case('13') DUI @break
                            @case('36') NIT @break
                            @case('03') Pasaporte @break
                            @case('02') Carnet de Residente @break
                            @case('37') Otro @break
                            @default —
                        @endswitch
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Número de documento</div>
                    <div class="detail-value">{{ $customer->document_number ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">NRC</div>
                    <div class="detail-value">{{ $customer->nrc ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Actividad económica</div>
                    <div class="detail-value">
                        @if($customer->EconomicActivity)
                            {{ $customer->EconomicActivity->code }} - {{ $customer->EconomicActivity->description }}
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Retiene IVA</div>
                    <div class="detail-value">
                        @if($customer->retains_iva)
                            <span class="badge-iva">✓ Sí, retiene IVA</span>
                        @else
                            <span style="color: var(--muted);">✗ No retiene</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Contacto --}}
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:1rem">◉ Información de contacto</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">
                        @if($customer->email)
                            <a href="mailto:{{ $customer->email }}" style="color: var(--accent); text-decoration: none;">
                                {{ $customer->email }}
                            </a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Teléfono</div>
                    <div class="detail-value">
                        @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}" style="color: var(--accent); text-decoration: none;">
                                {{ $customer->phone }}
                            </a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Dirección</div>
                    <div class="detail-value">{{ $customer->address ?: '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Ubicación geográfica --}}
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:1rem">◈ Ubicación</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">País</div>
                    <div class="detail-value">{{ $customer->country?->name ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Departamento</div>
                    <div class="detail-value">{{ $customer->municipality?->department?->name ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Municipio</div>
                    <div class="detail-value">{{ $customer->municipality?->name ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel lateral --}}
    <div style="position:sticky; top:1rem;">
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:0.75rem">◎ Resumen</div>
            <div class="detail-item">
                <div class="detail-label">Total facturas</div>
                <div class="detail-value" style="font-size: 1.2rem;">{{ $customer->invoices->count() }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Monto total facturado</div>
                <div class="detail-value" style="font-size: 1.2rem; color: var(--accent);">
                    ${{ number_format($customer->invoices->sum('total'), 2) }}
                </div>
            </div>
            @php
                $pendingInvoices = $customer->invoices->where('payment_status', 'pending');
            @endphp
            @if($pendingInvoices->count() > 0)
                <div class="detail-item">
                    <div class="detail-label">Facturas pendientes</div>
                    <div class="detail-value" style="color: var(--warn);">
                        {{ $pendingInvoices->count() }} factura(s) - ${{ number_format($pendingInvoices->sum('total'), 2) }}
                    </div>
                </div>
            @endif
        </div>

        <div class="detail-card">
            <div class="card-title" style="margin-bottom:0.75rem">◎ Metadatos</div>
            <div class="detail-item">
                <div class="detail-label">Creado</div>
                <div class="detail-value">{{ $customer->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Última actualización</div>
                <div class="detail-value">{{ $customer->updated_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div class="detail-card">
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary" style="justify-content: center; width: 100%;">
                    ➕ Crear factura para este cliente
                </a>
                <button type="button" class="btn btn-danger" style="justify-content: center; width: 100%;" onclick="confirmDelete()">
                    🗑️ Eliminar cliente
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" action="{{ route('customers.destroy', $customer) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDelete() {
        if (confirm('¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endsection