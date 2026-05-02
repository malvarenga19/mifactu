@extends('layouts.app')

@section('title', 'Facturas')
@section('breadcrumb', 'Facturación / <strong>Listado</strong>')

@section('topbar-actions')
    <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">+ Nueva factura</a>
@endsection

@push('styles')
<style>
    .search-container {
        margin-bottom: 1.5rem;
        display: flex;
        gap: 1rem;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .search-group {
        flex: 1;
        min-width: 160px;
    }
    .search-group label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--muted);
        margin-bottom: 0.25rem;
        display: block;
    }
    .search-group input, .search-group select {
        width: 100%;
    }
    .invoices-table {
        width: 100%;
        border-collapse: collapse;
    }
    .invoices-table th {
        font-family: var(--mono);
        font-size: 0.68rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .invoices-table td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .badge-draft { background: rgba(100, 100, 100, 0.15); color: #666; }
    .badge-issued { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .badge-cancelled { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .badge-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge-paid { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .badge-overdue { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .badge-received { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
    .badge-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .badge-pending-mh { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .pagination-info {
        margin-top: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        color: var(--muted);
    }
    .pagination-links {
        display: flex;
        gap: 0.3rem;
    }
    .pagination-links a, .pagination-links span {
        padding: 0.4rem 0.8rem;
        border-radius: var(--radius);
        background: var(--surface2);
        text-decoration: none;
        color: var(--text);
        font-size: 0.85rem;
        cursor: pointer;
    }
    .pagination-links a:hover {
        background: var(--accent);
        color: white;
    }
    .pagination-links .active {
        background: var(--accent);
        color: white;
    }
    .loading {
        text-align: center;
        padding: 2rem;
        color: var(--muted);
    }
    .total-amount {
        font-family: var(--mono);
        font-weight: 700;
        text-align: right;
    }
    .filter-active {
        background: var(--accent);
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
        font-size: 0.7rem;
    }
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-content {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        max-width: 400px;
        width: 90%;
        padding: 1.5rem;
    }
    .modal-content h4 {
        margin-bottom: 1rem;
        font-family: var(--mono);
        color: var(--danger);
    }
    .modal-content p {
        margin-bottom: 1.5rem;
    }
    .modal-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span class="card-title">◉ Listado de facturas</span>
        <div id="today-indicator" class="today-indicator" style="display: flex;">
            📅 {{ date('d/m/Y') }}
        </div>
    </div>

    {{-- Filtros de búsqueda --}}
    <div class="search-container">
        <div class="search-group">
            <label>🔍 Buscar</label>
            <input type="text" id="search-input" placeholder="Correlativo o cliente..." autocomplete="off">
        </div>
        <div class="search-group">
            <label>📄 Tipo factura</label>
            <select id="invoice-type-filter">
                <option value="">Todos los tipos</option>
                @foreach($invoiceTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-group">
            <label>📊 Estado</label>
            <select id="status-filter">
                <option value="">Todos</option>
                <option value="draft">Borrador</option>
                <option value="issued">Emitida</option>
                <option value="cancelled">Anulada</option>
            </select>
        </div>
        <div class="search-group">
            <label>💰 Estado pago</label>
            <select id="payment-status-filter">
                <option value="">Todos</option>
                <option value="pending">Pendiente</option>
                <option value="paid">Pagada</option>
                <option value="overdue">Vencida</option>
            </select>
        </div>
        <div class="search-group">
            <label>📅 Desde</label>
            <input type="date" id="date-from-filter">
        </div>
        <div class="search-group">
            <label>📅 Hasta</label>
            <input type="date" id="date-to-filter">
        </div>
        <div class="search-group">
            <label>&nbsp;</label>
            <button type="button" id="reset-filters" class="btn btn-secondary btn-sm">⟳ Limpiar</button>
        </div>
    </div>

    {{-- Tabla de resultados --}}
    <div class="table-wrap">
        <table class="invoices-table">
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
                    <th style="width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="invoices-tbody">
                <tr>
                    <td colspan="10" class="loading">Cargando facturas...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-info">
        <div id="pagination-info-text">Mostrando 0 registros</div>
        <div id="pagination-links" class="pagination-links"></div>
    </div>
</div>

{{-- Modal para anular factura --}}
<div id="cancel-modal" style="display:none;">
    <div class="modal-overlay" onclick="closeCancelModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h4>⚠️ Anular factura</h4>
            <p id="cancel-modal-message">¿Estás seguro de anular esta factura?<br><small style="color:var(--danger)">Esta acción no se puede deshacer.</small></p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Cancelar</button>
                <form id="cancel-form" method="POST" action="" style="margin:0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-danger">Anular factura</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let isLoading = false;
let searchTimeout = null;

const paymentMethods = {
    'cash': 'Efectivo',
    'credit_card': 'Tarjeta',
    'bank_transfer': 'Transferencia',
    'credit': 'Crédito'
};

const statusLabels = {
    'draft': 'Borrador',
    'issued': 'Emitida',
    'cancelled': 'Anulada'
};

const paymentStatusLabels = {
    'pending': 'Pendiente',
    'paid': 'Pagada',
    'overdue': 'Vencida'
};

function getMhStatusClass(status) {
    const classes = {
        'received': 'received',
        'rejected': 'rejected',
        'pending': 'pending-mh'
    };
    return classes[status] || 'pending-mh';
}

function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('es');
}

function escapeHtml(str) {
    if (!str) return str;
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function fetchInvoices() {
    if (isLoading) return;
    isLoading = true;

    const search = document.getElementById('search-input').value;
    const invoiceTypeId = document.getElementById('invoice-type-filter').value;
    const status = document.getElementById('status-filter').value;
    const paymentStatus = document.getElementById('payment-status-filter').value;
    const dateFrom = document.getElementById('date-from-filter').value;
    const dateTo = document.getElementById('date-to-filter').value;

    const params = new URLSearchParams({
        page: currentPage,
        search: search,
        invoice_type_id: invoiceTypeId,
        status: status,
        payment_status: paymentStatus,
        date_from: dateFrom,
        date_to: dateTo
    });



    const tbody = document.getElementById('invoices-tbody');
    tbody.innerHTML = '<tr><td colspan="10" class="loading">Cargando facturas...</td></tr>';

    fetch(`{{ route('invoices.index') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la petición');
        }
        return response.json();
    })
    .then(data => {
        renderTable(data.data);
        renderPagination(data);
        isLoading = false;
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = '<tr><td colspan="10" class="loading">Error al cargar datos. Refresca la página.</td></tr>';
        isLoading = false;
    });
}

function renderTable(invoices) {
    const tbody = document.getElementById('invoices-tbody');
    
    if (!invoices || invoices.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="loading">No se encontraron facturas para hoy</td></tr>';
        return;
    }

    tbody.innerHTML = invoices.map(inv => {
        const customerName = inv.customer?.name || '—';
        const companyName = inv.customer?.company_name;
        
        return `
            <tr>
                <td><code style="background:var(--surface2);padding:0.2rem 0.4rem;border-radius:4px;color:var(--accent);">${escapeHtml(inv.correlative)}</code></td>
                <td><span style="color:var(--muted);font-size:0.82rem;">${escapeHtml(inv.invoice_type?.name || '—')}</span></td>
                <td>
                    <div style="font-weight:500">${escapeHtml(customerName)}</div>
                    ${companyName ? `<div style="font-size:0.78rem;color:var(--muted)">${escapeHtml(companyName)}</div>` : ''}
                </td>
                <td style="font-family:var(--mono);font-size:0.8rem;">${formatDate(inv.issue_date)}</td>
                <td><span style="font-size:0.82rem;">${paymentMethods[inv.payment_method] || inv.payment_method}</span></td>
                <td class="total-amount">$${Number(inv.total_amount).toFixed(2)}</td>
                <td><span class="badge badge-${inv.status}">${statusLabels[inv.status] || inv.status}</span></td>
                <td><span class="badge badge-${inv.payment_status}">${paymentStatusLabels[inv.payment_status] || inv.payment_status}</span></td>
                <td><span class="badge badge-${getMhStatusClass(inv.status_mh)}" style="font-size:0.65rem;">${(inv.status_mh || 'pending').toUpperCase()}</span></td>
                <td>
                    <div style="display:flex;gap:0.3rem;">
                        <a href="/invoices/${inv.id}" class="btn btn-sm btn-secondary" style="padding:0.2rem 0.5rem;">👁️ Ver</a>
                        ${inv.status === 'draft' ? `<a href="/invoices/${inv.id}/edit" class="btn btn-sm btn-primary" style="padding:0.2rem 0.5rem;">✏️</a>` : ''}
                        ${inv.status === 'issued' ? `<button onclick="confirmCancel(${inv.id}, '${escapeHtml(inv.correlative).replace(/'/g, "\\'")}')" class="btn btn-sm btn-danger" style="padding:0.2rem 0.5rem;">🚫</button>` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(data) {
    const infoText = document.getElementById('pagination-info-text');
    const linksDiv = document.getElementById('pagination-links');
    
    infoText.textContent = `Mostrando ${data.from || 0} - ${data.to || 0} de ${data.total || 0} registros`;
    
    if (data.last_page <= 1) {
        linksDiv.innerHTML = '';
        return;
    }
    
    let links = '';
    
    if (data.prev_page_url) {
        links += `<a href="#" data-page="${data.current_page - 1}">← Anterior</a>`;
    } else {
        links += `<span style="opacity:0.5;">← Anterior</span>`;
    }
    
    for (let i = 1; i <= data.last_page; i++) {
        if (i === data.current_page) {
            links += `<span class="active">${i}</span>`;
        } else if (Math.abs(i - data.current_page) <= 2 || i === 1 || i === data.last_page) {
            links += `<a href="#" data-page="${i}">${i}</a>`;
        } else if (Math.abs(i - data.current_page) === 3) {
            links += `<span>...</span>`;
        }
    }
    
    if (data.next_page_url) {
        links += `<a href="#" data-page="${data.current_page + 1}">Siguiente →</a>`;
    } else {
        links += `<span style="opacity:0.5;">Siguiente →</span>`;
    }
    
    linksDiv.innerHTML = links;
    
    document.querySelectorAll('#pagination-links a[data-page]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            currentPage = parseInt(link.dataset.page);
            fetchInvoices();
        });
    });
}

function confirmCancel(id, correlative) {
    const modal = document.getElementById('cancel-modal');
    const message = document.getElementById('cancel-modal-message');
    const form = document.getElementById('cancel-form');
    
    message.innerHTML = `¿Anular la factura <strong>${escapeHtml(correlative)}</strong>?<br><small style="color:var(--danger)">Esta acción no se puede deshacer.</small>`;
    form.action = `/invoices/${id}`;
    modal.style.display = 'block';
}

function closeCancelModal() {
    document.getElementById('cancel-modal').style.display = 'none';
}

function triggerSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage = 1;
        fetchInvoices();
    }, 350);
}

// Event listeners
document.getElementById('search-input').addEventListener('input', triggerSearch);
document.getElementById('invoice-type-filter').addEventListener('change', () => { currentPage = 1; fetchInvoices(); });
document.getElementById('status-filter').addEventListener('change', () => { currentPage = 1; fetchInvoices(); });
document.getElementById('payment-status-filter').addEventListener('change', () => { currentPage = 1; fetchInvoices(); });
document.getElementById('date-from-filter').addEventListener('change', () => { currentPage = 1; fetchInvoices(); });
document.getElementById('date-to-filter').addEventListener('change', () => { currentPage = 1; fetchInvoices(); });

document.getElementById('reset-filters').addEventListener('click', () => {
    document.getElementById('search-input').value = '';
    document.getElementById('invoice-type-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('payment-status-filter').value = '';
    document.getElementById('date-from-filter').value = '';
    document.getElementById('date-to-filter').value = '';
    currentPage = 1;
    fetchInvoices();
});

window.onclick = function(event) {
    if (event.target.classList && event.target.classList.contains('modal-overlay')) {
        closeCancelModal();
    }
};

// Initial load
fetchInvoices();
</script>
@endpush