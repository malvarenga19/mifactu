@extends('layouts.app')

@section('title', 'Ver Proveedor: ' . $supplier->name)
@section('breadcrumb', 'Compras / <strong>' . e($supplier->name) . '</strong>')

@section('topbar-actions')
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary btn-sm">✏️ Editar</a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">← Volver</a>
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
    .badge-economic {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    .badge-active {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }
    .badge-inactive {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
</style>
@endpush

@section('content')
<div style="display:grid; grid-template-columns: 1fr 300px; gap:1.5rem; align-items:start">

    {{-- Columna principal --}}
    <div>
        {{-- Datos del proveedor --}}
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:1rem">◈ Datos del proveedor</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Nombre / Razón social</div>
                    <div class="detail-value">{{ $supplier->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Número de documento</div>
                    <div class="detail-value">{{ $supplier->document_number ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Actividad económica</div>
                    <div class="detail-value">
                        @if($supplier->economicActivity)
                            <span class="badge-economic">
                                {{ $supplier->economicActivity->code ?? '' }} {{ $supplier->economicActivity->description ?? '' }}
                            </span>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Estado</div>
                    <div class="detail-value">
                        @if($supplier->is_active ?? true)
                            <span class="badge-active">✓ Activo</span>
                        @else
                            <span class="badge-inactive">✗ Inactivo</span>
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
                        @if($supplier->email)
                            <a href="mailto:{{ $supplier->email }}" style="color: var(--accent); text-decoration: none;">
                                {{ $supplier->email }}
                            </a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Teléfono</div>
                    <div class="detail-value">
                        @if($supplier->phone)
                            <a href="tel:{{ $supplier->phone }}" style="color: var(--accent); text-decoration: none;">
                                {{ $supplier->phone }}
                            </a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Dirección</div>
                    <div class="detail-value">{{ $supplier->address ?: '—' }}</div>
                </div>
                @if($supplier->contact_person)
                <div class="detail-item">
                    <div class="detail-label">Persona de contacto</div>
                    <div class="detail-value">{{ $supplier->contact_person }}</div>
                </div>
                @endif
                @if($supplier->contact_phone)
                <div class="detail-item">
                    <div class="detail-label">Teléfono de contacto</div>
                    <div class="detail-value">{{ $supplier->contact_phone }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Ubicación geográfica --}}
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:1rem">◈ Ubicación</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">País</div>
                    <div class="detail-value">{{ $supplier->country?->name ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Departamento</div>
                    <div class="detail-value">{{ $supplier->municipality?->department?->name ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Municipio</div>
                    <div class="detail-value">{{ $supplier->municipality?->name ?: '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Datos bancarios (opcional) --}}
        @if($supplier->bank_name || $supplier->bank_account)
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:1rem">◈ Información bancaria</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Banco</div>
                    <div class="detail-value">{{ $supplier->bank_name ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Número de cuenta</div>
                    <div class="detail-value">{{ $supplier->bank_account ?: '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tipo de cuenta</div>
                    <div class="detail-value">{{ $supplier->bank_account_type ?: '—' }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Panel lateral --}}
    <div style="position:sticky; top:1rem;">
        <div class="detail-card">
            <div class="card-title" style="margin-bottom:0.75rem">◎ Metadatos</div>
            <div class="detail-item">
                <div class="detail-label">Creado</div>
                <div class="detail-value">{{ $supplier->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Última actualización</div>
                <div class="detail-value">{{ $supplier->updated_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div class="detail-card">
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <button type="button" class="btn btn-danger" style="justify-content: center; width: 100%;" onclick="confirmDelete()">
                    🗑️ Eliminar proveedor
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" action="{{ route('suppliers.destroy', $supplier) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDelete() {
        if (confirm('¿Estás seguro de eliminar este proveedor? Esta acción no se puede deshacer.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endsection