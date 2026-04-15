@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">
            <i class="fas fa-boxes text-primary"></i> Productos
        </h4>
        
        <!-- Buscador y Filtros SIN RECARGAR -->
        <div class="d-flex gap-2 flex-wrap">
            <div class="input-group" style="width: 300px;">
                <input type="text" 
                       id="searchInput" 
                       class="form-control" 
                       placeholder="Buscar producto..."
                       autocomplete="off">
                <button id="searchBtn" class="btn btn-outline-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
                <button id="clearSearch" class="btn btn-outline-secondary" type="button" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <select id="categoryFilter" class="form-select" style="width: 150px;">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            
            <div class="form-check ms-2">
                <input type="checkbox" id="lowStockFilter" class="form-check-input">
                <label class="form-check-label" for="lowStockFilter">
                    Stock Bajo
                </label>
            </div>
        </div>
        
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Producto
        </a>
    </div>
    
    <div class="card-body">
        <!-- Spinner de carga -->
        <div id="loadingSpinner" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Buscando productos...</p>
        </div>
        
        <!-- Contenedor de la tabla -->
        <div id="tableContainer">
            @include('products.partials.table', ['products' => $products])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Función para cargar productos vía AJAX
    function loadProducts() {
        const search = $('#searchInput').val();
        const category_id = $('#categoryFilter').val();
        const low_stock = $('#lowStockFilter').is(':checked') ? 1 : '';
        const per_page = $('#perPageSelect').val();
        
        // Mostrar spinner
        $('#loadingSpinner').show();
        $('#tableContainer').hide();
        
        $.ajax({
            url: '{{ route("products.index") }}',
            type: 'GET',
            data: {
                search: search,
                category_id: category_id,
                low_stock: low_stock,
                per_page: per_page,
                ajax: true
            },
            success: function(response) {
                $('#tableContainer').html(response);
                $('#loadingSpinner').hide();
                $('#tableContainer').show();
                
                // Mostrar/ocultar botón limpiar
                if (search || category_id || low_stock) {
                    $('#clearSearch').show();
                } else {
                    $('#clearSearch').hide();
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                $('#loadingSpinner').hide();
                $('#tableContainer').show();
                alert('Error al cargar los productos');
            }
        });
    }
    
    // Debounce para evitar muchas peticiones
    let debounceTimer;
    function debounceLoad() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadProducts, 300);
    }
    
    // Eventos de búsqueda en tiempo real
    $('#searchInput').on('keyup', function(e) {
        debounceLoad();
    });
    
    $('#searchBtn').on('click', function() {
        loadProducts();
    });
    
    $('#categoryFilter').on('change', function() {
        loadProducts();
    });
    
    $('#lowStockFilter').on('change', function() {
        loadProducts();
    });
    
    // Limpiar filtros
    $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        $('#categoryFilter').val('');
        $('#lowStockFilter').prop('checked', false);
        loadProducts();
    });
    
    // Selector de registros por página (delegación de eventos)
    $(document).on('change', '#perPageSelect', function() {
        loadProducts();
    });
    
    // Paginación mediante AJAX (delegación de eventos)
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        
        // Agregar parámetro ajax
        url += (url.indexOf('?') === -1 ? '?' : '&') + 'ajax=true';
        
        // Agregar filtros actuales
        url += '&search=' + encodeURIComponent($('#searchInput').val());
        url += '&category_id=' + $('#categoryFilter').val();
        url += '&low_stock=' + ($('#lowStockFilter').is(':checked') ? 1 : '');
        url += '&per_page=' + $('#perPageSelect').val();
        
        $('#loadingSpinner').show();
        $('#tableContainer').hide();
        
        $.get(url, function(response) {
            $('#tableContainer').html(response);
            $('#loadingSpinner').hide();
            $('#tableContainer').show();
        });
    });
</script>
@endpush