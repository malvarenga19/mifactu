@extends('layouts.app')

@section('title', 'Proveedores')
@section('breadcrumb', 'Compras / <strong>Proveedores</strong>')

@section('topbar-actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">+ Nuevo proveedor</a>
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
    .suppliers-table {
        width: 100%;
        border-collapse: collapse;
    }
    .suppliers-table th {
        font-family: var(--mono);
        font-size: 0.68rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .suppliers-table td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .badge-economic {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
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
    .supplier-name {
        font-weight: 600;
    }
    .supplier-doc {
        font-family: var(--mono);
        font-size: 0.75rem;
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
    <div class="card-header">
        <span class="card-title">◉ Listado de proveedores</span>
    </div>

    {{-- Filtros de búsqueda --}}
    <div class="search-container">
        <div class="search-group">
            <label>🔍 Buscar</label>
            <input type="text" id="search-input" placeholder="Nombre o documento..." autocomplete="off">
        </div>
        <div class="search-group">
            <label>🏢 Departamento</label>
            <select id="department-filter">
                <option value="">Todos los departamentos</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-group">
            <label>📍 Municipio</label>
            <select id="municipality-filter">
                <option value="">Todos los municipios</option>
            </select>
        </div>
        <div class="search-group">
            <label>&nbsp;</label>
            <button type="button" id="reset-filters" class="btn btn-secondary btn-sm">⟳ Limpiar</button>
        </div>
    </div>

    {{-- Tabla de resultados --}}
    <div class="table-wrap">
        <table class="suppliers-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Actividad económica</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Ubicación</th>
                    <th style="width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="suppliers-tbody">
                <tr>
                    <td colspan="7" class="loading">Cargando proveedores...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-info">
        <div id="pagination-info-text">Mostrando 0 registros</div>
        <div id="pagination-links" class="pagination-links"></div>
    </div>
</div>

{{-- Modal de confirmación (plantilla dinámica) --}}
<div id="delete-modal" style="display:none;">
    <div class="modal-overlay" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h4>Confirmar eliminación</h4>
            <p id="delete-modal-message">¿Eliminar este proveedor?<br><small style="color:var(--danger)">Esta acción no se puede deshacer.</small></p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <form id="delete-form" method="POST" action="" style="margin:0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
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
let currentDeleteId = null;

function fetchSuppliers() {
    if (isLoading) return;
    isLoading = true;

    const search = document.getElementById('search-input').value;
    const departmentId = document.getElementById('department-filter').value;
    const municipalityId = document.getElementById('municipality-filter').value;

    const params = new URLSearchParams({
        page: currentPage,
        search: search,
        department_id: departmentId,
        municipality_id: municipalityId
    });

    fetch(`{{ route('suppliers.index') }}?${params.toString()}`, {
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
        document.getElementById('suppliers-tbody').innerHTML = '<tr><td colspan="7" class="loading">Error al cargar datos</td></tr>';
        isLoading = false;
    });
}

function renderTable(suppliers) {
    const tbody = document.getElementById('suppliers-tbody');
    
    if (!suppliers || suppliers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading">No se encontraron proveedores</td></tr>';
        return;
    }

    tbody.innerHTML = suppliers.map(supplier => {
        const location = [];
        if (supplier.municipality) {
            location.push(supplier.municipality.name);
            if (supplier.municipality.department) {
                location.push(supplier.municipality.department.name);
            }
        }
        
        return `
            <tr>
                <td class="supplier-name">${escapeHtml(supplier.name)}</td>
                <td class="supplier-doc">${escapeHtml(supplier.document_number || '—')}</td>
                <td>${supplier.economic_activity ? `<span class="badge-economic">${escapeHtml(supplier.economic_activity.description || supplier.economic_activity)}</span>` : '—'}</td>
                <td style="font-size:0.8rem">${supplier.email ? `<a href="mailto:${escapeHtml(supplier.email)}" style="color:var(--accent);">${escapeHtml(supplier.email)}</a>` : '—'}</td>
                <td>${escapeHtml(supplier.phone || '—')}</td>
                <td style="font-size:0.75rem;color:var(--muted)">${escapeHtml(location.join(', ') || '—')}</td>
                <td style="display: flex; gap: 0.3rem;">
                    <a href="/suppliers/${supplier.id}" class="btn btn-sm btn-secondary" style="padding:0.2rem 0.5rem;">👁️</a>
                    <a href="/suppliers/${supplier.id}/edit" class="btn btn-sm btn-primary" style="padding:0.2rem 0.5rem;">✏️</a>
                    <button type="button" class="btn btn-sm btn-danger" style="padding:0.2rem 0.5rem;" onclick="confirmDelete(${supplier.id}, '${escapeHtml(supplier.name).replace(/'/g, "\\'")}')">🗑️</button>
                </td>
            </tr>
        `;
    }).join('');
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
            fetchSuppliers();
        });
    });
}

function confirmDelete(id, name) {
    const modal = document.getElementById('delete-modal');
    const message = document.getElementById('delete-modal-message');
    const form = document.getElementById('delete-form');
    
    message.innerHTML = `¿Eliminar el proveedor <strong>${escapeHtml(name)}</strong>?<br><small style="color:var(--danger)">Esta acción no se puede deshacer.</small>`;
    form.action = `/suppliers/${id}`;
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('delete-modal').style.display = 'none';
}

// Cargar municipios al cambiar departamento
document.getElementById('department-filter').addEventListener('change', function() {
    const deptId = this.value;
    const munSelect = document.getElementById('municipality-filter');
    
    if (deptId) {
        fetch(`/api/municipalities/by-department/${deptId}`)
            .then(response => response.json())
            .then(data => {
                munSelect.innerHTML = '<option value="">Todos los municipios</option>';
                data.forEach(mun => {
                    munSelect.innerHTML += `<option value="${mun.id}">${escapeHtml(mun.name)}</option>`;
                });
            })
            .catch(() => {
                munSelect.innerHTML = '<option value="">Todos los municipios</option>';
            });
    } else {
        munSelect.innerHTML = '<option value="">Todos los municipios</option>';
    }
    currentPage = 1;
    fetchSuppliers();
});

// Event listeners
document.getElementById('search-input').addEventListener('input', () => {
    currentPage = 1;
    fetchSuppliers();
});

document.getElementById('municipality-filter').addEventListener('change', () => {
    currentPage = 1;
    fetchSuppliers();
});

document.getElementById('reset-filters').addEventListener('click', () => {
    document.getElementById('search-input').value = '';
    document.getElementById('department-filter').value = '';
    document.getElementById('municipality-filter').innerHTML = '<option value="">Todos los municipios</option>';
    currentPage = 1;
    fetchSuppliers();
});

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    if (event.target.classList && event.target.classList.contains('modal-overlay')) {
        closeModal();
    }
};

// Initial load
fetchSuppliers();
</script>
@endpush