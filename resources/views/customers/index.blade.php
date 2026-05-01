@extends('layouts.app')

@section('title', 'Clientes')
@section('breadcrumb', 'Clientes / <strong>Listado</strong>')

@section('topbar-actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">+ Nuevo cliente</a>
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
        min-width: 200px;
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
    .customers-table {
        width: 100%;
        border-collapse: collapse;
    }
    .customers-table th {
        font-family: var(--mono);
        font-size: 0.68rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .customers-table td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .badge-iva {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(255, 193, 7, 0.15);
        color: var(--warn);
    }
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
    .customer-name {
        font-weight: 600;
    }
    .customer-company {
        font-size: 0.8rem;
        color: var(--muted);
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">◉ Listado de clientes</span>
    </div>

    {{-- Filtros de búsqueda --}}
    <div class="search-container">
        <div class="search-group">
            <label>🔍 Buscar</label>
            <input type="text" id="search-input" placeholder="Nombre, empresa, NIT, DUI, email..." autocomplete="off">
        </div>
        <div class="search-group">
            <label>📄 Tipo de documento</label>
            <select id="document-filter">
                <option value="">Todos</option>
                <option value="13">DUI</option>
                <option value="36">NIT</option>
                <option value="03">Pasaporte</option>
                <option value="02">Carnet de Residente</option>
                <option value="37">Otro</option>
            </select>
        </div>
        <div class="search-group">
            <label>💰 Retiene IVA</label>
            <select id="retains-filter">
                <option value="">Todos</option>
                <option value="1">Sí retiene</option>
                <option value="0">No retiene</option>
            </select>
        </div>
        <div class="search-group">
            <label>&nbsp;</label>
            <button type="button" id="reset-filters" class="btn btn-secondary btn-sm">⟳ Limpiar</button>
        </div>
    </div>

    {{-- Tabla de resultados --}}
    <div class="table-wrap">
        <table class="customers-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Número</th>
                    <th>NRC</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Retiene IVA</th>
                    <th style="width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="customers-tbody">
                <tr>
                    <td colspan="8" class="loading">Cargando clientes...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-info">
        <div id="pagination-info-text">Mostrando 0 registros</div>
        <div id="pagination-links" class="pagination-links"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let isLoading = false;

function getDocumentTypeLabel(type) {
    const types = {
        '13': 'DUI',
        '36': 'NIT',
        '03': 'Pasaporte',
        '02': 'Carnet Res.',
        '37': 'Otro'
    };
    return types[type] || type || '—';
}

function fetchCustomers() {
    if (isLoading) return;
    isLoading = true;

    const search = document.getElementById('search-input').value;
    const docType = document.getElementById('document-filter').value;  // ✅ Cambiado de 'document' a 'docType'
    const retains = document.getElementById('retains-filter').value;

    const params = new URLSearchParams({
        page: currentPage,
        search: search,
        document: docType,  // ✅ Usar docType aquí
        retains_iva: retains
    });

    fetch(`{{ route('customers.index') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        renderTable(data.data);
        renderPagination(data);
        isLoading = false;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('customers-tbody').innerHTML = '<tr><td colspan="8" class="loading">Error al cargar datos</td></tr>';
        isLoading = false;
    });
}

function renderTable(customers) {
    const tbody = document.getElementById('customers-tbody');
    
    if (!customers || customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="loading">No se encontraron clientes</td></tr>';
        return;
    }

    tbody.innerHTML = customers.map(customer => {
        const nameDisplay = customer.company_name ? 
            `<div class="customer-name">${escapeHtml(customer.name)}</div><div class="customer-company">${escapeHtml(customer.company_name)}</div>` : 
            `<div class="customer-name">${escapeHtml(customer.name)}</div>`;
        
        return `
            <tr>
                <td>${nameDisplay}</td>
                <td>${getDocumentTypeLabel(customer.document)}</td>
                <td>${escapeHtml(customer.document_number || '—')}</td>
                <td>${escapeHtml(customer.nrc || '—')}</td>
                <td>${customer.email ? `<a href="mailto:${escapeHtml(customer.email)}" style="color:var(--accent);">${escapeHtml(customer.email)}</a>` : '—'}</td>
                <td>${escapeHtml(customer.phone || '—')}</td>
                <td>${customer.retains_iva ? '<span class="badge-iva">✓ Retiene</span>' : '—'}</td>
                <td style="display: flex; gap: 0.3rem;">
                    <a href="/customers/${customer.id}" class="btn btn-sm btn-secondary" style="padding:0.2rem 0.5rem;">👁️</a>
                    <a href="/customers/${customer.id}/edit" class="btn btn-sm btn-primary" style="padding:0.2rem 0.5rem;">✏️</a>
                    <button type="button" class="btn btn-sm btn-danger" style="padding:0.2rem 0.5rem;" onclick="deleteCustomer(${customer.id})">🗑️</button>
                </td>
            </tr>
        `;
    }).join('');
}

// Función auxiliar para evitar XSS
function escapeHtml(str) {
    if (!str) return str;
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
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
            fetchCustomers();
        });
    });
}

function deleteCustomer(id) {
    if (confirm('¿Estás seguro de eliminar este cliente? Se eliminarán también sus facturas asociadas.')) {
        fetch(`/customers/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchCustomers();
            } else {
                alert('Error al eliminar: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al eliminar el cliente');
        });
    }
}

// Event listeners
document.getElementById('search-input').addEventListener('input', () => {
    currentPage = 1;
    fetchCustomers();
});

document.getElementById('document-filter').addEventListener('change', () => {
    currentPage = 1;
    fetchCustomers();
});

document.getElementById('retains-filter').addEventListener('change', () => {
    currentPage = 1;
    fetchCustomers();
});

document.getElementById('reset-filters').addEventListener('click', () => {
    document.getElementById('search-input').value = '';
    document.getElementById('document-filter').value = '';
    document.getElementById('retains-filter').value = '';
    currentPage = 1;
    fetchCustomers();
});

// Initial load
fetchCustomers();
</script>
@endpush