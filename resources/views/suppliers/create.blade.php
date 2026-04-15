@extends('layouts.app')

@section('title', 'Nuevo Proveedor')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="fas fa-truck text-success"></i> Nuevo Proveedor
            </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Columna 1 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre *</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document_number" class="form-label">NIT / Documento</label>
                            <input type="text" name="document_number" id="document_number" class="form-control @error('document_number') is-invalid @enderror" value="{{ old('document_number') }}">
                            @error('document_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Columna 2 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="activity_search" class="form-label">Actividad Económica</label>
                            <input type="text" id="activity_search" class="form-control" placeholder="Escriba actividad..." list="activities" autocomplete="off">
                            <input type="hidden" name="activity_id" id="activity_id">
                            <datalist id="activities">
                                @foreach($economicActivities as $activity)
                                    <option value="{{ $activity->code }} - {{ $activity->description }}" data-id="{{ $activity->id }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="col-md-12">
                        <hr>
                        <h5 class="mb-3">Ubicación</h5>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="country_id" class="form-label">País *</label>
                            <select name="country_id" id="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
                                <option value="">Seleccione país</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ $country->name === 'El Salvador' ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Departamento</label>
                            <select id="department_id" name="department_id" class="form-select">
                                <option value="">Seleccione departamento</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="municipality_id" class="form-label">Municipio</label>
                            <select name="municipality_id" id="municipality_id" class="form-select @error('municipality_id') is-invalid @enderror">
                                <option value="">Seleccione municipio</option>
                            </select>
                            @error('municipality_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar Proveedor</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Datalist para actividad económica
            const input = document.getElementById('activity_search');
            const hidden = document.getElementById('activity_id');
            const options = Array.from(document.querySelectorAll('#activities option'));

            input.addEventListener('input', function () {
                const match = options.find(opt => opt.value === this.value);
                hidden.value = match ? match.dataset.id : '';
            });

            input.addEventListener('change', function () {
                const match = options.find(opt => opt.value === this.value);
                hidden.value = match ? match.dataset.id : '';
            });
        </script>
        
        <script>
            // Municipios y ubicación
            const municipalities = @json($municipalities);
            const elSalvadorId = @json($countries->firstWhere('name', 'El Salvador')?->id);
            
            const countrySelect = document.getElementById('country_id');
            const departmentSelect = document.getElementById('department_id');
            const municipioSelect = document.getElementById('municipality_id');

            function loadMunicipalitiesByDepartment(deptId) {
                deptId = parseInt(deptId);
                municipioSelect.innerHTML = '<option value="">Seleccione municipio</option>';
                
                if (deptId) {
                    let filtered = municipalities.filter(m => m.department_id === deptId);
                    if (filtered.length > 0) {
                        filtered.forEach(m => {
                            municipioSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                        });
                        municipioSelect.disabled = false;
                    } else {
                        municipioSelect.innerHTML = '<option value="">No hay municipios</option>';
                        municipioSelect.disabled = true;
                    }
                } else {
                    municipioSelect.disabled = true;
                }
            }

            countrySelect.addEventListener('change', function () {
                let countryId = parseInt(this.value);
                if (countryId == elSalvadorId) {
                    departmentSelect.disabled = false;
                    departmentSelect.value = '';
                    municipioSelect.innerHTML = '<option value="">Seleccione municipio</option>';
                    municipioSelect.disabled = true;
                } else if (countryId && countryId !== elSalvadorId) {
                    departmentSelect.disabled = true;
                    departmentSelect.value = '1';
                    loadMunicipalitiesByDepartment(1);
                } else {
                    departmentSelect.disabled = false;
                    departmentSelect.value = '';
                    municipioSelect.innerHTML = '<option value="">Seleccione municipio</option>';
                    municipioSelect.disabled = true;
                }
            });

            departmentSelect.addEventListener('change', function () {
                loadMunicipalitiesByDepartment(this.value);
            });

            document.addEventListener('DOMContentLoaded', function () {
                if (countrySelect.value == elSalvadorId) {
                    departmentSelect.disabled = false;
                } else if (countrySelect.value && countrySelect.value != elSalvadorId) {
                    departmentSelect.disabled = true;
                    departmentSelect.value = '1';
                    loadMunicipalitiesByDepartment(1);
                }
            });
        </script>
    @endpush
@endsection