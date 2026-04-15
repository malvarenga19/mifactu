@extends('layouts.app')

@section('title', 'Editar Proveedor')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="fas fa-truck text-warning"></i> Editar Proveedor
            </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre *</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $supplier->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document_number" class="form-label">NIT / Documento</label>
                            <input type="text" name="document_number" id="document_number"
                                class="form-control @error('document_number') is-invalid @enderror"
                                value="{{ old('document_number', $supplier->document_number) }}">
                            @error('document_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $supplier->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $supplier->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="activity_search" class="form-label">Actividad Económica</label>
                            <input type="text" id="activity_search"
                                class="form-control @error('activity_id') is-invalid @enderror"
                                placeholder="Escriba actividad..." list="activities" autocomplete="off"
                                value="{{ old('activity_search', $supplier->economicActivity?->description) }}">
                            <input type="hidden" name="activity_id" id="activity_id"
                                value="{{ old('activity_id', $supplier->activity_id) }}">
                            <datalist id="activities">
                                @foreach($economicActivities as $activity)
                                    <option value="{{ $activity->code }} - {{ $activity->description }}"
                                        data-id="{{ $activity->id }}">
                                @endforeach
                            </datalist>
                            @error('activity_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea name="address" id="address" rows="3"
                                class="form-control @error('address') is-invalid @enderror">{{ old('address', $supplier->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <hr>
                        <h5 class="mb-3">Ubicación</h5>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="country_id" class="form-label">País *</label>
                            <select name="country_id" id="country_id"
                                class="form-select @error('country_id') is-invalid @enderror" required>
                                <option value="">Seleccione país</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ old('country_id', $supplier->country_id) == $country->id ? 'selected' : '' }}>
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
                                    <option value="{{ $dept->id }}" {{ old('department_id', $supplier->municipality?->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="municipality_id" class="form-label">Municipio</label>
                            <select name="municipality_id" id="municipality_id"
                                class="form-select @error('municipality_id') is-invalid @enderror">
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
                    <button type="submit" class="btn btn-primary">Actualizar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Datalist simple para actividad económica (como en clientes)
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

        // Inicializar el ID al cargar la página
        document.addEventListener('DOMContentLoaded', function () {
            const match = options.find(opt => opt.value === input.value);
            if (match) {
                hidden.value = match.dataset.id;
            }
        });
    </script>

    <script>
        // Municipios y ubicación (igual como está)
        const municipalities = @json($municipalities);
        const elSalvadorId = @json($countries->firstWhere('name', 'El Salvador')?->id);

        function loadMunicipalities(deptId) {
            const municipioSelect = $('#municipality_id');
            municipioSelect.empty().append('<option value="">Seleccione municipio</option>');
            if (deptId) {
                const filtered = municipalities.filter(m => m.department_id == deptId);
                filtered.forEach(m => {
                    const selected = ({{ old('municipality_id', $supplier->municipality_id) }} == m.id) ? 'selected' : '';
                    municipioSelect.append(`<option value="${m.id}" ${selected}>${m.name}</option>`);
                });
                municipioSelect.prop('disabled', false);
            } else {
                municipioSelect.prop('disabled', true);
            }
        }

        $('#country_id').on('change', function () {
            const countryId = parseInt($(this).val());
            const departmentSelect = $('#department_id');
            if (countryId === elSalvadorId) {
                departmentSelect.prop('disabled', false);
                departmentSelect.val('{{ old('department_id', $supplier->municipality?->department_id) }}').trigger('change');
            } else if (countryId && countryId !== elSalvadorId) {
                departmentSelect.prop('disabled', true).val('1').trigger('change');
                loadMunicipalities(1);
            } else {
                departmentSelect.prop('disabled', false).val('').trigger('change');
                loadMunicipalities(null);
            }
        });

        $('#department_id').on('change', function () {
            loadMunicipalities($(this).val());
        });

        $(document).ready(function () {
            const initialCountry = $('#country_id').val();
            if (initialCountry && parseInt(initialCountry) !== elSalvadorId) {
                $('#department_id').prop('disabled', true).val('1');
                loadMunicipalities(1);
            } else {
                const initialDept = $('#department_id').val();
                if (initialDept) loadMunicipalities(initialDept);
            }
        });
    </script>
@endpush