@extends('layouts.app')

@section('title', 'Productos')
@section('breadcrumb', 'Productos / <strong>Listado</strong>')

@section('topbar-actions')
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">+ Nuevo producto</a>
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

        .search-group input,
        .search-group select {
            width: 100%;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th {
            font-family: var(--mono);
            font-size: 0.68rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }

        .products-table td {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .product-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: var(--radius);
            background: var(--surface2);
        }

        .badge-stock {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-low {
            background: rgba(255, 92, 92, 0.15);
            color: var(--danger);
        }

        .badge-normal {
            background: rgba(0, 200, 100, 0.15);
            color: var(--success);
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

        .pagination-links a,
        .pagination-links span {
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

        .stock-low {
            color: var(--danger);
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <span class="card-title">◉ Listado de productos</span>
        </div>

        {{-- Filtros de búsqueda --}}
        <div class="search-container">
            <div class="search-group">
                <label>🔍 Buscar</label>
                <input type="text" id="search-input" placeholder="Nombre, código o SKU..." autocomplete="off">
            </div>
            <div class="search-group">
                <label>📁 Categoría</label>
                <select id="category-filter">
                    <option value="">Todas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="search-group">
                <label>📊 Stock</label>
                <select id="stock-filter">
                    <option value="">Todos</option>
                    <option value="low">Stock bajo (mínimo)</option>
                    <option value="out">Agotados (stock = 0)</option>
                    <option value="normal">Stock normal</option>
                </select>
            </div>
            <div class="search-group">
                <label>&nbsp;</label>
                <button type="button" id="reset-filters" class="btn btn-secondary btn-sm">⟳ Limpiar</button>
            </div>
        </div>

        {{-- Tabla de resultados --}}
        <div class="table-wrap">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio venta</th>
                        <th>Stock</th>
                        <th>Equivalencias</th>
                        <th style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="products-tbody">
                    <tr>
                        <td colspan="8" class="loading">Cargando productos...</td>
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

        function escapeHtml(str) {
            if (!str) return str;
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
        let currentPage = 1;
        let isLoading = false;

        function fetchProducts() {
            if (isLoading) return;
            isLoading = true;

            const search = document.getElementById('search-input').value;
            const category = document.getElementById('category-filter').value;
            const stock = document.getElementById('stock-filter').value;

            const params = new URLSearchParams({
                page: currentPage,
                search: search,
                category: category,
                stock: stock
            });

            fetch(`{{ route('products.index') }}?${params.toString()}`, {
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
                    document.getElementById('products-tbody').innerHTML = '<tr><td colspan="8" class="loading">Error al cargar datos</td></tr>';
                    isLoading = false;
                });
        }

        function renderTable(products) {
            const tbody = document.getElementById('products-tbody');

            if (!products || products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="loading">No se encontraron productos</td></tr>';
                return;
            }

            tbody.innerHTML = products.map(product => {
                const stock = parseFloat(product.stock);
                const minStock = parseFloat(product.min_stock);
                const isLowStock = minStock > 0 && stock <= minStock;
                const isOutStock = stock <= 0;

                let stockClass = '';
                let stockText = stock.toFixed(2);
                if (isOutStock) {
                    stockClass = 'stock-low';
                    stockText = '0 (Agotado)';
                } else if (isLowStock) {
                    stockClass = 'stock-low';
                    stockText = `${stock.toFixed(2)} ⚠️`;
                }

                // Generar HTML de códigos equivalentes
                let equivalentsHtml = '';
                if (product.equivalents && product.equivalents.length > 0) {
                    equivalentsHtml = `
                    <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                        ${product.equivalents.map(equiv =>
                        `<span style="background: var(--surface2); padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.7rem;">
                                ${escapeHtml(equiv.equivalent_code)}
                            </span>`
                    ).join('')}
                    </div>
                `;
                } else {
                    equivalentsHtml = '<span class="text-muted" style="font-size:0.75rem;">Sin equivalentes</span>';
                }

                return `
                <tr>
                    <td>
                        ${product.image_path ?
                        `<img src="/storage/${product.image_path}" class="product-image" alt="${product.name}" onerror="this.src='https://via.placeholder.com/40?text=No'">` :
                        `<div style="width:40px;height:40px;background:var(--surface2);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;color:var(--muted);">📷</div>`
                    }
                    </td>
                    <td>${product.code || '—'}</td>
                    <td>${product.name}</td>
                    <td>${product.category ? product.category.name : '—'}</td>
                    <td>$${parseFloat(product.sale_price).toFixed(2)}</td>
                    <td class="${stockClass}">${stockText}</td>
                    <td>${equivalentsHtml}</td>
                    <td style="display: flex; gap: 0.3rem;">
                        <a href="/products/${product.id}" class="btn btn-sm btn-secondary" style="padding:0.2rem 0.5rem;">👁️</a>
                        <a href="/products/${product.id}/edit" class="btn btn-sm btn-primary" style="padding:0.2rem 0.5rem;">✏️</a>
                        <button type="button" class="btn btn-sm btn-danger" style="padding:0.2rem 0.5rem;" onclick="deleteProduct(${product.id})">🗑️</button>
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

            // Previous
            if (data.prev_page_url) {
                links += `<a href="#" data-page="${data.current_page - 1}">← Anterior</a>`;
            } else {
                links += `<span style="opacity:0.5;">← Anterior</span>`;
            }

            // Page numbers
            for (let i = 1; i <= data.last_page; i++) {
                if (i === data.current_page) {
                    links += `<span class="active">${i}</span>`;
                } else if (Math.abs(i - data.current_page) <= 2 || i === 1 || i === data.last_page) {
                    links += `<a href="#" data-page="${i}">${i}</a>`;
                } else if (Math.abs(i - data.current_page) === 3) {
                    links += `<span>...</span>`;
                }
            }

            // Next
            if (data.next_page_url) {
                links += `<a href="#" data-page="${data.current_page + 1}">Siguiente →</a>`;
            } else {
                links += `<span style="opacity:0.5;">Siguiente →</span>`;
            }

            linksDiv.innerHTML = links;

            // Bind click events
            document.querySelectorAll('#pagination-links a[data-page]').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    currentPage = parseInt(link.dataset.page);
                    fetchProducts();
                });
            });
        }

        function deleteProduct(id) {
            if (confirm('¿Estás seguro de eliminar este producto?')) {
                fetch(`/products/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            fetchProducts();
                        } else {
                            alert('Error al eliminar: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error al eliminar el producto');
                    });
            }
        }

        // Event listeners
        document.getElementById('search-input').addEventListener('input', () => {
            currentPage = 1;
            fetchProducts();
        });

        document.getElementById('category-filter').addEventListener('change', () => {
            currentPage = 1;
            fetchProducts();
        });

        document.getElementById('stock-filter').addEventListener('change', () => {
            currentPage = 1;
            fetchProducts();
        });

        document.getElementById('reset-filters').addEventListener('click', () => {
            document.getElementById('search-input').value = '';
            document.getElementById('category-filter').value = '';
            document.getElementById('stock-filter').value = '';
            currentPage = 1;
            fetchProducts();
        });

        // Initial load
        fetchProducts();
    </script>
@endpush