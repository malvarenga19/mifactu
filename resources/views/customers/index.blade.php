@extends('layouts.app')

@section('title', 'Listado de Clientes')

@section('content')
<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">
            <i class="fas fa-users text-primary"></i> Clientes
        </h4>
        
        <!-- Buscador y Filtros SIN RECARGAR -->
        <div class="d-flex gap-2 flex-wrap">
            <div class="input-group" style="width: 300px;">
                <input type="text" 
                       id="searchInput" 
                       class="form-control" 
                       placeholder="Buscar cliente por nombre, empresa, documento, NRC..."
                       autocomplete="off">
                <button id="searchBtn" class="btn btn-outline-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
                <button id="clearSearch" class="btn btn-outline-secondary" type="button" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <select id="documentFilter" class="form-select" style="width: 150px;">
                <option value="">Todos los documentos</option>
                <option value="13">DUI</option>
                <option value="36">NIT</option>
                <option value="03">Pasaporte</option>
                <option value="02">Carnet de Residente</option>
                <option value="37">Otro</option>
            </select>
            
            <div class="form-check ms-2">
                <input type="checkbox" id="retainsIvaFilter" class="form-check-input">
                <label class="form-check-label" for="retainsIvaFilter">
                    Retiene IVA
                </label>
            </div>
        </div>
        
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Cliente
        </a>
    </div>
    
    <div class="card-body">
        <!-- Spinner de carga -->
        <div id="loadingSpinner" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Buscando clientes...</p>
        </div>
        
        <!-- Contenedor de la tabla -->
        <div id="tableContainer">
            @include('customers.partials.table', ['customers' => $customers])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Función para cargar clientes vía AJAX
    function loadCustomers() {
        const search = $('#searchInput').val();
        const document_type = $('#documentFilter').val();
        const retains_iva = $('#retainsIvaFilter').is(':checked') ? 1 : '';
        const per_page = $('#perPageSelect').val();
        
        // Mostrar spinner
        $('#loadingSpinner').show();
        $('#tableContainer').hide();
        
        $.ajax({
            url: '{{ route("customers.index") }}',
            type: 'GET',
            data: {
                search: search,
                document_type: document_type,
                retains_iva: retains_iva,
                per_page: per_page,
                ajax: true
            },
            success: function(response) {
                $('#tableContainer').html(response);
                $('#loadingSpinner').hide();
                $('#tableContainer').show();
                
                // Mostrar/ocultar botón limpiar
                if (search || document_type || retains_iva) {
                    $('#clearSearch').show();
                } else {
                    $('#clearSearch').hide();
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                $('#loadingSpinner').hide();
                $('#tableContainer').show();
                alert('Error al cargar los clientes');
            }
        });
    }
    
    // Debounce para evitar muchas peticiones
    let debounceTimer;
    function debounceLoad() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadCustomers, 300);
    }
    
    // Eventos de búsqueda en tiempo real
    $('#searchInput').on('keyup', function(e) {
        debounceLoad();
    });
    
    $('#searchBtn').on('click', function() {
        loadCustomers();
    });
    
    $('#documentFilter').on('change', function() {
        loadCustomers();
    });
    
    $('#retainsIvaFilter').on('change', function() {
        loadCustomers();
    });
    
    // Limpiar filtros
    $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        $('#documentFilter').val('');
        $('#retainsIvaFilter').prop('checked', false);
        loadCustomers();
    });
    
    // Selector de registros por página (delegación de eventos)
    $(document).on('change', '#perPageSelect', function() {
        loadCustomers();
    });
    
    // Paginación mediante AJAX (delegación de eventos)
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        
        // Agregar parámetro ajax
        url += (url.indexOf('?') === -1 ? '?' : '&') + 'ajax=true';
        
        // Agregar filtros actuales
        url += '&search=' + encodeURIComponent($('#searchInput').val());
        url += '&document_type=' + $('#documentFilter').val();
        url += '&retains_iva=' + ($('#retainsIvaFilter').is(':checked') ? 1 : '');
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