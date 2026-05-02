@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', '<strong>Dashboard</strong>')

@push('styles')
<style>
    /* ── KPI cards ── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .kpi {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.2rem 1.4rem;
        position: relative;
        overflow: hidden;
    }
    .kpi::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
    }
    .kpi.accent::before  { background: var(--accent); }
    .kpi.cyan::before    { background: var(--accent2); }
    .kpi.warn::before    { background: var(--warn); }
    .kpi.danger::before  { background: var(--danger); }
    .kpi-label {
        font-family: var(--mono);
        font-size: .68rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: .5rem;
    }
    .kpi-value {
        font-family: var(--mono);
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1;
    }
    .kpi.accent  .kpi-value { color: var(--accent); }
    .kpi.cyan    .kpi-value { color: var(--accent2); }
    .kpi.warn    .kpi-value { color: var(--warn); }
    .kpi.danger  .kpi-value { color: var(--danger); }
    .kpi-sub {
        margin-top: .45rem;
        font-size: .78rem;
        color: var(--muted);
    }
    .kpi-sub .up   { color: var(--success); }
    .kpi-sub .down { color: var(--danger); }

    /* ── Grid principal ── */
    .dash-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.5rem;
    }
    .dash-left  { display: flex; flex-direction: column; gap: 1.5rem; }
    .dash-right { display: flex; flex-direction: column; gap: 1.5rem; }

    /* ── Chart ── */
    .chart-wrap { position: relative; height: 200px; }

    /* ── Top productos ── */
    .top-bar-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: .55rem;
    }
    .top-bar-label {
        font-size: .82rem;
        min-width: 130px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .top-bar-track {
        flex: 1;
        background: var(--surface2);
        border-radius: 20px;
        height: 6px;
        overflow: hidden;
    }
    .top-bar-fill {
        height: 100%;
        border-radius: 20px;
        background: var(--accent);
        transition: width .4s ease;
    }
    .top-bar-val {
        font-family: var(--mono);
        font-size: .78rem;
        color: var(--muted);
        min-width: 60px;
        text-align: right;
    }

    /* ── Stock bajo ── */
    .stock-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .5rem 0;
        border-bottom: 1px solid var(--border);
        font-size: .85rem;
    }
    .stock-row:last-child { border-bottom: none; }
    .stock-qty {
        font-family: var(--mono);
        font-weight: 700;
        font-size: .9rem;
    }
    .stock-qty.cero   { color: var(--danger); }
    .stock-qty.low    { color: var(--warn); }

    /* ── Últimas facturas ── */
    .mini-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    .mini-table th {
        font-family: var(--mono);
        font-size: .65rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        padding: .4rem .6rem;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .mini-table td {
        padding: .55rem .6rem;
        border-bottom: 1px solid var(--border);
    }
    .mini-table tbody tr:last-child td { border-bottom: none; }
    .mini-table tbody tr:hover { background: var(--surface2); }

    @media (max-width: 900px) {
        .dash-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $mesActual = \Carbon\Carbon::now()->translatedFormat('F Y');
@endphp

{{-- ── KPIs ── --}}
<div class="kpi-grid">
    <div class="kpi accent">
        <div class="kpi-label">Ventas hoy</div>
        <div class="kpi-value">${{ number_format($ventasHoy, 2) }}</div>
        <div class="kpi-sub">{{ \Carbon\Carbon::today()->translatedFormat('d \d\e F') }}</div>
    </div>

    <div class="kpi accent">
        <div class="kpi-label">Ventas {{ $mesActual }}</div>
        <div class="kpi-value">${{ number_format($ventasMes, 2) }}</div>
        <div class="kpi-sub">
            @if($variacionMes !== null)
                @if($variacionMes >= 0)
                    <span class="up">▲ {{ $variacionMes }}%</span> vs mes anterior
                @else
                    <span class="down">▼ {{ abs($variacionMes) }}%</span> vs mes anterior
                @endif
            @else
                Sin datos mes anterior
            @endif
        </div>
    </div>

    <div class="kpi warn">
        <div class="kpi-label">Pendientes de pago</div>
        <div class="kpi-value">{{ $facturasPendientes }}</div>
        <div class="kpi-sub">${{ number_format($montoPendiente, 2) }} por cobrar</div>
    </div>

    <div class="kpi danger">
        <div class="kpi-label">Facturas vencidas</div>
        <div class="kpi-value">{{ $facturasVencidas }}</div>
        <div class="kpi-sub">${{ number_format($montoVencido, 2) }} en riesgo</div>
    </div>

    <div class="kpi {{ $productosAgotados > 0 ? 'danger' : 'cyan' }}">
        <div class="kpi-label">Stock agotado</div>
        <div class="kpi-value">{{ $productosAgotados }}</div>
        <div class="kpi-sub">
            <span style="color:var(--warn)">{{ $productosBajoStock }}</span> con stock bajo
        </div>
    </div>
</div>

{{-- ── Grid principal ── --}}
<div class="dash-grid">
    <div class="dash-left">

        {{-- Gráfica ventas diarias --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">◈ Ventas diarias — {{ $mesActual }}</span>
                <span style="font-size:.78rem;color:var(--muted)">Total del mes: <strong style="color:var(--accent)">${{ number_format($ventasMes,2) }}</strong></span>
            </div>
            <div class="chart-wrap">
                <canvas id="chartVentas"></canvas>
            </div>
        </div>

        {{-- Últimas facturas --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">◉ Últimas facturas</span>
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm">Ver todas</a>
            </div>
            <div style="overflow-x:auto">
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Correlativo</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th style="text-align:right">Total</th>
                            <th>Estado</th>
                            <th>Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($ultimasFacturas as $f)
                        <tr style="cursor:pointer" onclick="window.location='{{ route('invoices.show', $f->id) }}'">
                            <td style="font-family:var(--mono);font-size:.78rem;color:var(--accent)">{{ $f->correlative }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($f->cliente, 22) }}</td>
                            <td style="font-family:var(--mono);font-size:.78rem">{{ \Carbon\Carbon::parse($f->issue_date)->format('d/m/Y') }}</td>
                            <td style="text-align:right;font-family:var(--mono);font-weight:700">${{ number_format($f->total_amount,2) }}</td>
                            <td>
                                @php $sc = ['draft'=>'badge-draft','issued'=>'badge-issued','cancelled'=>'badge-cancelled']; @endphp
                                <span class="badge {{ $sc[$f->status] ?? 'badge-draft' }}">
                                    {{ ['draft'=>'Borrador','issued'=>'Emitida','cancelled'=>'Anulada'][$f->status] ?? $f->status }}
                                </span>
                            </td>
                            <td>
                                @php $pc = ['pending'=>'badge-pending','paid'=>'badge-paid','overdue'=>'badge-overdue']; @endphp
                                <span class="badge {{ $pc[$f->payment_status] ?? '' }}">
                                    {{ ['pending'=>'Pendiente','paid'=>'Pagada','overdue'=>'Vencida'][$f->payment_status] ?? $f->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:1.5rem">Sin facturas aún</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dash-right">

        {{-- Top productos --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">◎ Más vendidos</span>
                <span style="font-size:.72rem;color:var(--muted)">{{ $mesActual }}</span>
            </div>
            @php $maxVenta = $topProductos->max('total_vendido') ?: 1; @endphp
            @forelse($topProductos as $p)
                <div class="top-bar-row">
                    <div class="top-bar-label" title="{{ $p->name }}">
                        @if($p->code)<span style="font-family:var(--mono);font-size:.72rem;color:var(--muted)">[{{ $p->code }}]</span> @endif
                        {{ \Illuminate\Support\Str::limit($p->name, 18) }}
                    </div>
                    <div class="top-bar-track">
                        <div class="top-bar-fill" style="width:{{ round(($p->total_vendido/$maxVenta)*100) }}%"></div>
                    </div>
                    <div class="top-bar-val">${{ number_format($p->total_vendido,0) }}</div>
                </div>
                <div style="font-size:.72rem;color:var(--muted);margin:-0.3rem 0 .6rem 0;padding-left:0">
                    {{ number_format($p->unidades,0) }} unidades
                </div>
            @empty
                <div style="color:var(--muted);font-size:.85rem;padding:.5rem 0">Sin ventas este mes.</div>
            @endforelse
        </div>

        {{-- Stock bajo --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">◇ Stock crítico</span>
                @if($productosAgotados > 0)
                    <span class="badge badge-cancelled">{{ $productosAgotados }} agotado{{ $productosAgotados>1?'s':'' }}</span>
                @endif
            </div>
            @forelse($listaStockBajo as $p)
                <div class="stock-row">
                    <div>
                        <div style="font-size:.85rem;font-weight:500">{{ $p->name }}</div>
                        @if($p->code)
                            <div style="font-size:.72rem;color:var(--muted);font-family:var(--mono)">{{ $p->code }}</div>
                        @endif
                    </div>
                    <div>
                        <span class="stock-qty {{ $p->stock <= 0 ? 'cero' : 'low' }}">
                            {{ $p->stock }}
                        </span>
                        @if($p->min_stock)
                            <span style="font-size:.72rem;color:var(--muted)"> / mín {{ $p->min_stock }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div style="color:var(--success);font-size:.85rem;padding:.5rem 0">✓ Todo el stock está bien.</div>
            @endforelse
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function() {
    const dias   = @json(array_column($diasMes, 'fecha'));
    const totals = @json(array_column($diasMes, 'total'));
    const counts = @json(array_column($diasMes, 'cantidad'));

    const ctx = document.getElementById('chartVentas').getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(232,197,71,0.25)');
    gradient.addColorStop(1, 'rgba(232,197,71,0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dias,
            datasets: [{
                label: 'Ventas $',
                data: totals,
                borderColor: '#e8c547',
                backgroundColor: gradient,
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#e8c547',
                pointHoverRadius: 5,
                fill: true,
                tension: 0.35,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#22222c',
                    borderColor: '#2e2e3a',
                    borderWidth: 1,
                    titleColor: '#7a7a9a',
                    bodyColor: '#e8c547',
                    callbacks: {
                        title: (items) => 'Día ' + items[0].label,
                        label: (item) => ' $' + item.raw.toFixed(2),
                        afterLabel: (item) => ' ' + counts[item.dataIndex] + ' factura(s)',
                    }
                },
            },
            scales: {
                x: {
                    grid: { color: '#2e2e3a' },
                    ticks: { color: '#7a7a9a', font: { family: 'Space Mono', size: 10 } },
                },
                y: {
                    grid: { color: '#2e2e3a' },
                    ticks: {
                        color: '#7a7a9a',
                        font: { family: 'Space Mono', size: 10 },
                        callback: v => '$' + v.toLocaleString(),
                    },
                    beginAtZero: true,
                }
            }
        }
    });
})();
</script>
@endpush