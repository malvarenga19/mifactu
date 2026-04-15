@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="fas fa-user-edit text-warning"></i> Editar Cliente
            </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.update', $customer) }}" method="POST" id="customerForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Columna 1 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre *</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Nombre de Empresa</label>
                            <input type="text" name="company_name" id="company_name"
                                class="form-control @error('company_name') is-invalid @enderror"
                                value="{{ old('company_name', $customer->company_name) }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">Tipo de Documento</label>
                            <select name="document" id="document"
                                class="form-select @error('document') is-invalid @enderror">
                                <option value="">Seleccione tipo</option>
                                <option value="13" {{ old('document', $customer->document) == '13' ? 'selected' : '' }}>DUI
                                </option>
                                <option value="36" {{ old('document', $customer->document) == '36' ? 'selected' : '' }}>NIT
                                </option>
                                <option value="03" {{ old('document', $customer->document) == '03' ? 'selected' : '' }}>
                                    Pasaporte</option>
                                <option value="02" {{ old('document', $customer->document) == '02' ? 'selected' : '' }}>Carnet
                                    de Residente</option>
                                <option value="37" {{ old('document', $customer->document) == '37' ? 'selected' : '' }}>Otro
                                </option>
                            </select>
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document_number" class="form-label">Número de Documento</label>
                            <input type="text" name="document_number" id="document_number"
                                class="form-control @error('document_number') is-invalid @enderror"
                                value="{{ old('document_number', $customer->document_number) }}">
                            @error('document_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nrc" class="form-label">NRC</label>
                            <input type="text" name="nrc" id="nrc" class="form-control @error('nrc') is-invalid @enderror"
                                value="{{ old('nrc', $customer->nrc) }}">
                            @error('nrc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Columna 2 -->
                    <div class="col-md-6">
                        <!-- Actividad Económica con búsqueda -->
                        <div class="mb-3 position-relative">
                            <label for="activity_search" class="form-label">Actividad Económica</label>
                            <input type="text" id="activity_search"
                                class="form-control @error('activity_id') is-invalid @enderror"
                                placeholder="Escriba el nombre de la actividad..." autocomplete="off"
                                value="{{ old('activity_search', $customer->economicActivity?->description) }}">
                            <input type="hidden" name="activity_id" id="activity_id"
                                value="{{ old('activity_id', $customer->activity_id) }}">
                            <small class="text-muted">Escriba para buscar la actividad económica</small>
                            @error('activity_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $customer->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $customer->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea name="address" id="address" rows="2"
                                class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="retains_iva" id="retains_iva" value="1"
                                    class="form-check-input @error('retains_iva') is-invalid @enderror" {{ old('retains_iva', $customer->retains_iva) ? 'checked' : '' }}>
                                <label class="form-check-label" for="retains_iva">
                                    Retiene IVA
                                </label>
                                @error('retains_iva')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Fila completa para ubicación -->
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
                                    <option value="{{ $country->id }}" {{ old('country_id', $customer->country_id) == $country->id ? 'selected' : '' }}>
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
                                    <option value="{{ $dept->id }}" {{ old('department_id', $customer->municipality?->department_id) == $dept->id ? 'selected' : '' }}>
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
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Datos de actividades
        const economicActivities = @json($economicActivities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'name' => $activity->description
            ];
        }));

        // Datos de municipios
        const municipalities = @json($municipalities);

        // Obtener ID de El Salvador
        const elSalvadorId = @json($countries->firstWhere('name', 'El Salvador')?->id);

        // Elementos del DOM
        const searchInput = document.getElementById('activity_search');
        const hiddenInput = document.getElementById('activity_id');
        let suggestionsDiv = null;

        // Función para mostrar sugerencias de actividades
        function showSuggestions(matches, inputElement) {
            if (suggestionsDiv) {
                suggestionsDiv.remove();
                suggestionsDiv = null;
            }

            if (!matches || matches.length === 0) return;

            suggestionsDiv = document.createElement('div');
            suggestionsDiv.className = 'list-group position-absolute shadow';
            suggestionsDiv.style.zIndex = '1000';
            suggestionsDiv.style.maxHeight = '250px';
            suggestionsDiv.style.overflowY = 'auto';
            suggestionsDiv.style.backgroundColor = 'white';
            suggestionsDiv.style.border = '1px solid #ddd';
            suggestionsDiv.style.borderRadius = '4px';
            suggestionsDiv.style.width = inputElement.offsetWidth + 'px';

            matches.slice(0, 10).forEach(match => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = match.name;
                item.style.textAlign = 'left';
                item.style.padding = '8px 12px';
                item.style.border = 'none';
                item.style.borderBottom = '1px solid #eee';
                item.style.backgroundColor = 'white';
                item.style.cursor = 'pointer';
                item.style.width = '100%';

                item.addEventListener('mouseenter', () => {
                    item.style.backgroundColor = '#e9ecef';
                });
                item.addEventListener('mouseleave', () => {
                    item.style.backgroundColor = 'white';
                });

                item.addEventListener('click', () => {
                    searchInput.value = match.name;
                    hiddenInput.value = match.id;
                    if (suggestionsDiv) {
                        suggestionsDiv.remove();
                        suggestionsDiv = null;
                    }
                });

                suggestionsDiv.appendChild(item);
            });

            const rect = inputElement.getBoundingClientRect();
            suggestionsDiv.style.position = 'fixed';
            suggestionsDiv.style.top = (rect.bottom + window.scrollY) + 'px';
            suggestionsDiv.style.left = (rect.left + window.scrollX) + 'px';

            document.body.appendChild(suggestionsDiv);
        }

        // Búsqueda de actividades
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();

                if (query === '') {
                    hiddenInput.value = '';
                    if (suggestionsDiv) {
                        suggestionsDiv.remove();
                        suggestionsDiv = null;
                    }
                    return;
                }

                const matches = economicActivities.filter(activity =>
                    activity.name.toLowerCase().includes(query)
                );

                const exactMatch = matches.find(m => m.name.toLowerCase() === query);
                if (exactMatch && matches.length === 1) {
                    hiddenInput.value = exactMatch.id;
                    if (suggestionsDiv) {
                        suggestionsDiv.remove();
                        suggestionsDiv = null;
                    }
                } else {
                    hiddenInput.value = '';
                    showSuggestions(matches, this);
                }
            });

            document.addEventListener('click', function (e) {
                if (e.target !== searchInput && suggestionsDiv) {
                    suggestionsDiv.remove();
                    suggestionsDiv = null;
                }
            });
        }

        // Funciones para ubicación
        function loadMunicipalities(deptId) {
            deptId = parseInt(deptId);
            const municipioSelect = $('#municipality_id');

            municipioSelect.empty().append('<option value="">Seleccione municipio</option>');

            if (deptId && !isNaN(deptId)) {
                const filteredMunicipalities = municipalities.filter(muni => muni.department_id === deptId);

                if (filteredMunicipalities.length > 0) {
                    filteredMunicipalities.forEach(muni => {
                        const selected = ({{ old('municipality_id', $customer->municipality_id) }} == muni.id) ? 'selected' : '';
                        municipioSelect.append(`<option value="${muni.id}" ${selected}>${muni.name}</option>`);
                    });
                    municipioSelect.prop('disabled', false);
                } else {
                    municipioSelect.append('<option value="">No hay municipios disponibles</option>');
                    municipioSelect.prop('disabled', true);
                }
            } else {
                municipioSelect.prop('disabled', true);
            }

            municipioSelect.trigger('change');
        }

        // Eventos de ubicación
        $('#country_id').on('change', function () {
            const countryId = parseInt($(this).val());
            const departmentSelect = $('#department_id');

            if (countryId === elSalvadorId) {
                departmentSelect.prop('disabled', false);
                departmentSelect.val('{{ old('department_id', $customer->municipality?->department_id) }}').trigger('change');
            } else if (countryId && countryId !== elSalvadorId) {
                departmentSelect.prop('disabled', true);
                departmentSelect.val('1').trigger('change');
                loadMunicipalities(1);
                $('#municipality_id').prop('disabled', false);
            } else {
                departmentSelect.prop('disabled', false);
                departmentSelect.val('').trigger('change');
                loadMunicipalities(null);
            }
        });

        $('#department_id').on('change', function () {
            const deptId = $(this).val();
            loadMunicipalities(deptId);
        });

        // Inicializar al cargar
        $(document).ready(function () {
            const initialCountry = $('#country_id').val();
            if (initialCountry && parseInt(initialCountry) !== elSalvadorId) {
                $('#department_id').prop('disabled', true);
                $('#department_id').val('1');
                loadMunicipalities(1);
            } else {
                const initialDept = $('#department_id').val();
                if (initialDept) {
                    loadMunicipalities(initialDept);
                }
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const documentTypeSelect = document.getElementById('document');
            const nrcInput = document.getElementById('nrc');
            const activitySearch = document.getElementById('activity_search');
            const activityHidden = document.getElementById('activity_id');

            // Función para validar requeridos según tipo de documento
            function updateRequiredFields() {
                const isNIT = documentTypeSelect.value === '36';

                if (isNIT) {
                    // Hacer NRC requerido
                    nrcInput.setAttribute('required', 'required');
                    nrcInput.classList.add('required-field');

                    // Hacer Actividad Económica requerida
                    activitySearch.setAttribute('required', 'required');
                    activityHidden.setAttribute('required', 'required');
                    activitySearch.classList.add('required-field');

                    // Agregar indicador visual
                    addRequiredIndicator(nrcInput, 'NRC');
                    addRequiredIndicator(activitySearch, 'Actividad Económica');
                } else {
                    // Quitar required
                    nrcInput.removeAttribute('required');
                    activitySearch.removeAttribute('required');
                    activityHidden.removeAttribute('required');
                    nrcInput.classList.remove('required-field');
                    activitySearch.classList.remove('required-field');

                    // Quitar indicador visual
                    removeRequiredIndicator(nrcInput);
                    removeRequiredIndicator(activitySearch);
                }
            }

            // Agregar asterisco visual
            function addRequiredIndicator(input, labelText) {
                const formGroup = input.closest('.mb-3');
                if (formGroup && !formGroup.querySelector('.required-asterisk')) {
                    const label = formGroup.querySelector('.form-label');
                    if (label && !label.innerHTML.includes('<span class="required-asterisk text-danger">*</span>')) {
                        const asterisk = document.createElement('span');
                        asterisk.className = 'required-asterisk text-danger';
                        asterisk.innerHTML = ' *';
                        label.appendChild(asterisk);
                    }
                }
            }

            // Quitar asterisco visual
            function removeRequiredIndicator(input) {
                const formGroup = input.closest('.mb-3');
                if (formGroup) {
                    const asterisk = formGroup.querySelector('.required-asterisk');
                    if (asterisk) {
                        asterisk.remove();
                    }
                }
            }

            // Validar antes de enviar el formulario
            const form = document.getElementById('customerForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const isNIT = documentTypeSelect.value === '36';

                    if (isNIT) {
                        let isValid = true;
                        let errorMessage = '';

                        // Validar NRC
                        if (!nrcInput.value.trim()) {
                            isValid = false;
                            errorMessage += '• El NRC es requerido cuando el tipo de documento es NIT\n';
                            nrcInput.classList.add('is-invalid');
                        } else {
                            nrcInput.classList.remove('is-invalid');
                        }

                        // Validar Actividad Económica
                        if (!activityHidden.value && !activitySearch.value.trim()) {
                            isValid = false;
                            errorMessage += '• La Actividad Económica es requerida cuando el tipo de documento es NIT\n';
                            activitySearch.classList.add('is-invalid');
                        } else if (!activityHidden.value && activitySearch.value.trim()) {
                            isValid = false;
                            errorMessage += '• Por favor seleccione una Actividad Económica válida de la lista\n';
                            activitySearch.classList.add('is-invalid');
                        } else {
                            activitySearch.classList.remove('is-invalid');
                        }

                        if (!isValid) {
                            e.preventDefault();
                            alert('Por favor complete los siguientes campos requeridos:\n' + errorMessage);
                        }
                    }
                });

                // Limpiar errores al escribir
                nrcInput.addEventListener('input', function () {
                    this.classList.remove('is-invalid');
                });

                activitySearch.addEventListener('input', function () {
                    this.classList.remove('is-invalid');
                });
            }

            // Escuchar cambios en el tipo de documento
            documentTypeSelect.addEventListener('change', updateRequiredFields);

            // Ejecutar al cargar la página
            updateRequiredFields();
        });
    </script>
@endpush