@extends('layouts.app')

@section('title', 'Nuevo Proveedor')
@section('breadcrumb', 'Proveedores / <strong>Nuevo</strong>')

@section('topbar-actions')
    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">← Volver</a>
@endsection

@push('styles')
<style>
    .form-group { margin-bottom: 1.5rem; }
    .form-label { font-weight: 500; margin-bottom: 0.3rem; display: block; }
    .form-hint { font-size: 0.7rem; color: var(--muted); margin-top: 0.2rem; display: block; }
    hr { margin: 1rem 0; border-color: var(--border); }
    .required-indicator::after { content: " *"; color: #dc3545; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('suppliers.store') }}" id="supplierForm">
    @csrf

    <div style="display:grid; grid-template-columns: 1fr 300px; gap:1.5rem; align-items:start">

        {{-- Columna principal --}}
        <div style="display:flex; flex-direction:column; gap:1.5rem">

            {{-- Datos del proveedor --}}
            <div class="card">
                <div class="card-title" style="margin-bottom:1.2rem">◈ Datos del proveedor</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required-indicator">Nombre *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIT / Documento</label>
                        <input type="text" name="document_number" value="{{ old('document_number') }}">
                        @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Actividad económica</label>
                        <input type="text" id="activity_search" value="{{ old('activity_search') }}" placeholder="Escriba actividad..." list="activities" autocomplete="off">
                        <input type="hidden" name="activity_id" id="activity_id" value="{{ old('activity_id') }}">
                        <datalist id="activities">
                            @foreach($economicActivities as $activity)
                                <option value="{{ $activity->code }} - {{ $activity->description }}" data-id="{{ $activity->id }}"></option>
                            @endforeach
                        </datalist>
                        @error('activity_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <textarea name="address" rows="2">{{ old('address') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Ubicación geográfica --}}
            <div class="card">
                <div class="card-title" style="margin-bottom:1.2rem">◉ Ubicación</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required-indicator">País *</label>
                        <select name="country_id" id="country_id" required>
                            <option value="">Seleccionar país…</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" @selected(old('country_id', $countries->firstWhere('name', 'El Salvador')?->id) == $country->id)>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departamento</label>
                        <select name="department_id" id="department_id">
                            <option value="">Seleccionar departamento…</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Municipio</label>
                        <select name="municipality_id" id="municipality_id">
                            <option value="">Seleccionar municipio…</option>
                            @if(old('municipality_id'))
                                @foreach($municipalities->where('id', old('municipality_id')) as $muni)
                                    <option value="{{ $muni->id }}" selected>{{ $muni->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('municipality_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel lateral --}}
        <div style="position:sticky; top:1rem;">
            <div class="card">
                <div class="card-title" style="margin-bottom:1rem">◎ Acciones</div>
                <div style="display:flex; flex-direction:column; gap:0.6rem;">
                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">✓ Guardar proveedor</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary" style="width:100%; justify-content:center;">Cancelar</a>
                </div>
                <hr>
                <div class="form-hint" style="text-align:center; margin-top:0.5rem;">
                    Los campos marcados con * son obligatorios.
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Actividad económica
    const input = document.getElementById('activity_search');
    const hidden = document.getElementById('activity_id');
    const options = Array.from(document.querySelectorAll('#activities option'));

    input.addEventListener('input', function () {
        const match = options.find(opt => opt.value === this.value);
        hidden.value = match ? match.dataset.id : '';
    });

    // Departamentos y municipios (El Salvador)
    const municipalities = @json($municipalities);
    const elSalvadorId = @json($countries->firstWhere('name', 'El Salvador')?->id);

    const countrySelect = document.getElementById('country_id');
    const departmentSelect = document.getElementById('department_id');
    const municipioSelect = document.getElementById('municipality_id');

    function resetDepartmentsAndMunicipalities() {
        departmentSelect.value = '';
        municipioSelect.innerHTML = '<option value="">Seleccionar municipio…</option>';
    }

    function loadMunicipalitiesByDepartment(deptId) {
        deptId = parseInt(deptId);
        if (deptId) {
            let filtered = municipalities.filter(muni => muni.department_id === deptId);
            municipioSelect.innerHTML = '<option value="">Seleccionar municipio…</option>';
            if (filtered.length > 0) {
                filtered.forEach(muni => {
                    municipioSelect.innerHTML += `<option value="${muni.id}">${muni.name}</option>`;
                });
            } else {
                municipioSelect.innerHTML = '<option value="">No hay municipios para este departamento</option>';
            }
        } else {
            municipioSelect.innerHTML = '<option value="">Primero seleccione un departamento</option>';
        }
        municipioSelect.disabled = false;
    }

    countrySelect.addEventListener('change', function () {
        let countryId = parseInt(this.value);
        if (countryId === elSalvadorId) {
            departmentSelect.disabled = false;
            resetDepartmentsAndMunicipalities();
        } else if (countryId && countryId !== elSalvadorId) {
            departmentSelect.disabled = true;
            departmentSelect.value = '';
            municipioSelect.innerHTML = '<option value="">Seleccione un país válido</option>';
            municipioSelect.disabled = true;
        } else {
            departmentSelect.disabled = false;
            resetDepartmentsAndMunicipalities();
        }
    });

    departmentSelect.addEventListener('change', function () {
        let deptId = this.value;
        loadMunicipalitiesByDepartment(deptId);
    });
</script>
@endpush