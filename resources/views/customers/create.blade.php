@extends('layouts.app')

@section('title', 'Nuevo Cliente')

@section('content')
    <div class="card shadow">
        <div class="card-header bg-white">
            <h4 class="mb-0">
                <i class="fas fa-user-plus text-success"></i> Nuevo Cliente
            </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.store') }}" method="POST" id="customerForm">
                @csrf

                <div class="row">
                    <!-- Columna 1 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre *</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Nombre de Empresa</label>
                            <input type="text" name="company_name" id="company_name"
                                class="form-control @error('company_name') is-invalid @enderror">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">Tipo de Documento</label>
                            <select name="document" id="document"
                                class="form-select @error('document') is-invalid @enderror">
                                <option value="">Seleccione tipo</option>
                                <option value="13">DUI</option>
                                <option value="36">NIT</option>
                                <option value="03">Pasaporte</option>
                                <option value="02">Carnet de Residente</option>
                                <option value="37">Otro</option>
                            </select>
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="document_number" class="form-label">Número de Documento</label>
                            <input type="text" name="document_number" id="document_number"
                                class="form-control @error('document_number') is-invalid @enderror">
                            @error('document_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nrc" class="form-label">NRC</label>
                            <input type="text" name="nrc" id="nrc" class="form-control @error('nrc') is-invalid @enderror">
                            @error('nrc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Columna 2 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="activity_search" class="form-label">Actividad Económica</label>

                            <input type="text" id="activity_search" class="form-control" placeholder="Escriba actividad..."
                                list="activities" autocomplete="off">

                            <input type="hidden" name="activity_id" id="activity_id">

                            <datalist id="activities">
                                @foreach($economicActivities as $activity)
                                    <option value="{{ $activity->code }} - {{ $activity->description }}"
                                        data-id="{{ $activity->id }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea name="address" id="address" rows="2"
                                class="form-control @error('address') is-invalid @enderror"></textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="retains_iva" id="retains_iva" value="1"
                                    class="form-check-input @error('retains_iva') is-invalid @enderror">
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
                            <select id="department_id" name="department_id" class="form-select" required>
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
                            <select name="municipality_id" id="municipality_id" required
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
                    <button type="submit" class="btn btn-success">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')

        <script>
            const input = document.getElementById('activity_search');
            const hidden = document.getElementById('activity_id');
            const options = Array.from(document.querySelectorAll('#activities option'));

            input.addEventListener('input', function () {
                const match = options.find(opt => opt.value === this.value);
                hidden.value = match ? match.dataset.id : '';
            });
        </script>
        <script>
            // Pasar los municipios de PHP a JavaScript
            const municipalities = @json($municipalities);

            // Obtener ID de El Salvador (buscando por nombre)
            const elSalvadorId = @json($countries->firstWhere('name', 'El Salvador')?->id);

            // Referencias a los elementos
            const countrySelect = document.getElementById('country_id');
            const departmentSelect = document.getElementById('department_id');
            const municipioSelect = document.getElementById('municipality_id');

            // Función para limpiar selects
            function resetDepartmentsAndMunicipalities() {
                // Resetear departamento al primer option válido
                departmentSelect.value = '';

                // Limpiar y resetear municipios
                municipioSelect.innerHTML = '<option value="">Seleccione municipio</option>';
            }

            // Función para cargar departamentos según el país
            function loadDepartmentsByCountry(countryId) {
                if (countryId == elSalvadorId) {
                    // Si es El Salvador, mostrar todos los departamentos
                    departmentSelect.disabled = false;
                    // Mantener los options originales (ya están en el HTML)
                } else {
                    // Si NO es El Salvador, poner departamento y municipio en 1 (o el primer valor)
                    if (departmentSelect.options.length > 0) {
                        departmentSelect.value = '1'; // ID 1 por defecto
                        departmentSelect.disabled = true;

                        // Cargar municipios del departamento 1
                        loadMunicipalitiesByDepartment(1);
                    }
                }
            }

            // Función para cargar municipios según el departamento
            function loadMunicipalitiesByDepartment(deptId) {
                deptId = parseInt(deptId);

                if (deptId) {
                    // Filtrar municipios por department_id
                    let filteredMunicipalities = municipalities.filter(muni => muni.department_id === deptId);

                    // Limpiar y llenar el select
                    municipioSelect.innerHTML = '<option value="">Seleccione municipio</option>';

                    if (filteredMunicipalities.length > 0) {
                        filteredMunicipalities.forEach(muni => {
                            municipioSelect.innerHTML += `<option value="${muni.id}">${muni.name}</option>`;
                        });
                    } else {
                        municipioSelect.innerHTML = '<option value="">No hay municipios para este departamento</option>';
                    }
                } else {
                    municipioSelect.innerHTML = '<option value="">Primero seleccione un departamento</option>';
                }

                if (deptId == 1) {
                    municipioSelect.value = '1'; // ID 1 por defecto
                    municipioSelect.disabled = true;
                } else {
                    municipioSelect.disabled = false;
                }
            }

            // Evento cuando cambia el país
            countrySelect.addEventListener('change', function () {
                let countryId = parseInt(this.value);

                if (countryId == elSalvadorId) {
                    // Es El Salvador - habilitar selección normal
                    departmentSelect.disabled = false;
                    departmentSelect.value = '';
                    resetDepartmentsAndMunicipalities();
                } else if (countryId && countryId !== elSalvadorId) {
                    // Es otro país - forzar department_id = 1 y municipality_id correspondiente
                    departmentSelect.disabled = true;

                    // Buscar si existe el departamento con ID 1
                    let deptOptionExists = false;
                    for (let i = 0; i < departmentSelect.options.length; i++) {
                        if (departmentSelect.options[i].value == '1') {
                            deptOptionExists = true;
                            break;
                        }
                    }

                    if (deptOptionExists) {
                        departmentSelect.value = '1';
                        loadMunicipalitiesByDepartment(1);
                    } else {
                        console.warn('No existe departamento con ID 1');
                        resetDepartmentsAndMunicipalities();
                    }
                } else {
                    // No hay país seleccionado
                    departmentSelect.disabled = false;
                    resetDepartmentsAndMunicipalities();
                }
            });

            // Evento cuando cambia el departamento
            departmentSelect.addEventListener('change', function () {
                let deptId = this.value;
                loadMunicipalitiesByDepartment(deptId);
            });

            // Inicializar al cargar la página
            document.addEventListener('DOMContentLoaded', function () {
                // Si el país por defecto es El Salvador, todo normal
                if (countrySelect.value == elSalvadorId) {
                    departmentSelect.disabled = false;
                }
                // Si por alguna razón el país por defecto NO es El Salvador, forzar IDs
                else if (countrySelect.value && countrySelect.value != elSalvadorId) {
                    departmentSelect.disabled = true;
                    departmentSelect.value = '1';
                    municipioSelect.disabled = true;
                    municipioSelect.value = '1';
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
@endsection