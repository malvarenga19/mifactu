{{-- resources/views/inventory_movements/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@section('content')
<div class="card shadow">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">
            <i class="fas fa-exchange-alt text-primary"></i> Movimientos de Inventario
        </h4>
        
        <div class="d-flex gap-2 flex-wrap">
            <div class="input-group" style="width: 250px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Buscar producto...">
                <button id="searchBtn" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <select id="typeFilter" class="form-select" style="width: 130px;">
                <option value="">Todos</option>
                <option value="entrada">Entradas</option>
                <option value="salida">Salidas</option>
                <option value="ajuste">Ajustes</option>
            </select>
            
            <input type="date" id="dateFrom" class="form-control" style="width: 130px;">
            <input type="date" id="dateTo" class="form-control" style="width: 130px;">
            
            <a href="{{ route('inventory_movements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Movimiento
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <div id="loadingSpinner" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Cargando...</p>
        </div>
        
        <div id="tableContainer">
            @include('inventory_movements.partials.table', ['movements' => $movements])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let isLoading = false;
let currentRequest = null;

function loadMovements(page = 1) {
    if (isLoading) return;
    
    const search = $('#searchInput').val();
    const type = $('#typeFilter').val();
    const dateFrom = $('#dateFrom').val();
    const dateTo = $('#dateTo').val();
    
    if (currentRequest) {
        currentRequest.abort();
    }
    
    isLoading = true;
    $('#loadingSpinner').show();
    $('#tableContainer').hide();
    
    currentRequest = $.ajax({
        url: '{{ route("inventory_movements.index") }}',
        type: 'GET',
        data: {
            search: search,
            type: type,
            date_from: dateFrom,
            date_to: dateTo,
            page: page,
            ajax: true
        },
        success: function(response) {
            $('#tableContainer').html(response);
            $('#loadingSpinner').hide();
            $('#tableContainer').fadeIn(200);
        },
        complete: function() {
            isLoading = false;
            currentRequest = null;
        }
    });
}

// Eventos
$('#searchBtn').on('click', () => loadMovements());
$('#typeFilter').on('change', () => loadMovements());
$('#dateFrom, #dateTo').on('change', () => loadMovements());

$('#searchInput').on('keypress', function(e) {
    if (e.which === 13) loadMovements();
});

$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    let page = new URL($(this).attr('href')).searchParams.get('page');
    loadMovements(page);
});
</script>
@endpush