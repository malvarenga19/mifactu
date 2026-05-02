@extends('layouts.app')

@section('title', 'Editar Cliente: ' . $customer->name)
@section('breadcrumb', 'Clientes / <strong>' . e($customer->name) . '</strong>')

@section('topbar-actions')
    <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">← Volver</a>
@endsection

@push('styles')
    <style>
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.3rem;
            display: block;
        }

        .form-hint {
            font-size: 0.7rem;
            color: var(--muted);
            margin-top: 0.2rem;
            display: block;
        }

        hr {
            margin: 1rem 0;
            border-color: var(--border);
        }

        .required-star::after {
            content: " *";
            color: var(--danger);
        }

        .required-indicator::after {
            content: " *";
            color: #dc3545;
        }
    </style>
@endpush

@section('content')
    <form method="POST" action="{{ route('customers.update', $customer) }}" id="customerForm">
        @csrf
        @method('PUT')

        <div style="display:grid; grid-template-columns: 1fr 300px; gap:1.5rem; align-items:start">

            {{-- Columna principal --}}
            <div style="display:flex; flex-direction:column; gap:1.5rem">

                {{-- Datos personales / empresa --}}
                <div class="card">
                    <div class="card-title" style="margin-bottom:1.2rem">◈ Datos del cliente</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required-star">Nombre completo</label>
                            <input type="text" name="name" value="{{ old('name', $customer->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nombre de empresa</label>
                            <input type="text" name="company_name"
                                value="{{ old('company_name', $customer->company_name) }}">
                            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo de documento</label>
                            <select name="document" id="document">
                                <option value="">Seleccionar tipo…</option>
                                <option value="13" @selected(old('document', $customer->document) == '13')>DUI</option>
                                <option value="36" @selected(old('document', $customer->document) == '36')>NIT</option>
                                <option value="03" @selected(old('document', $customer->document) == '03')>Pasaporte</option>
                                <option value="02" @selected(old('document', $customer->document) == '02')>Carnet de Residente
                                </option>
                                <option value="37" @selected(old('document', $customer->document) == '37')>Otro</option>
                            </select>
                            @error('document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" id="documentNumberLabel">Número de documento</label>
                            <input type="text" name="document_number" id="document_number"
                                value="{{ old('document_number', $customer->document_number) }}">
                            @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" id="nrcLabel">NRC</label>
                            <input type="text" name="nrc" id="nrc" value="{{ old('nrc', $customer->nrc) }}">
                            @error('nrc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" id="activityLabel">Actividad económica</label>
                            <input type="text" id="activity_search"
                                value="{{ old('activity_search', $customer->economicActivity ? $customer->economicActivity->code . ' - ' . $customer->economicActivity->description : '') }}"
                                placeholder="Escriba actividad..." list="activities" autocomplete="off">
                            <input type="hidden" name="activity_id" id="activity_id"
                                value="{{ old('activity_id', $customer->activity_id) }}">
                            <datalist id="activities">
                                @foreach($economicActivities as $activity)
                                    <option value="{{ $activity->code }} - {{ $activity->description }}"
                                        data-id="{{ $activity->id }}"></option>
                                @endforeach
                            </datalist>
                            @error('activity_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" id="emailLabel">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" id="phoneLabel">Teléfono</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $customer->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" id="addressLabel">Dirección</label>
                        <textarea name="address" id="address" rows="2">{{ old('address', $customer->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="checkbox" name="retains_iva" value="1" @checked(old('retains_iva', $customer->retains_iva)) style="width:auto;">
                            <span style="font-weight:normal;">Retiene IVA</span>
                        </label>
                        @error('retains_iva')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Ubicación geográfica --}}
                <div class="card">
                    <div class="card-title" style="margin-bottom:1.2rem">◉ Ubicación</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required-indicator">País</label>
                            <select name="country_id" id="country_id" required>
                                <option value="">Seleccionar país…</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" @selected(old('country_id', $customer->country_id) == $country->id)>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" id="departmentLabel">Departamento</label>
                            <select name="department_id" id="department_id">
                                <option value="">Seleccionar departamento…</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id', $customer->municipality?->department_id) == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" id="municipalityLabel">Municipio</label>
                            <select name="municipality_id" id="municipality_id">
                                <option value="">Seleccionar municipio…</option>
                                @if($customer->municipality_id)
                                    <option value="{{ $customer->municipality_id }}" selected>
                                        {{ $customer->municipality?->name }}</option>
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
                        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">✓ Guardar
                            cambios</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary"
                            style="width:100%; justify-content:center;">Cancelar</a>
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

        const currentMunicipalityId = {{ $customer->municipality_id ?? 'null' }};
        const currentDepartmentId = {{ $customer->municipality?->department_id ?? 'null' }};

        function loadMunicipalitiesByDepartment(deptId, selectedMuniId = null) {
            deptId = parseInt(deptId);
            if (deptId) {
                let filtered = municipalities.filter(muni => muni.department_id === deptId);
                municipioSelect.innerHTML = '<option value="">Seleccionar municipio…</option>';
                if (filtered.length > 0) {
                    filtered.forEach(muni => {
                        const selected = (selectedMuniId && parseInt(selectedMuniId) === muni.id) ? 'selected' : '';
                        municipioSelect.innerHTML += `<option value="${muni.id}" ${selected}>${muni.name}</option>`;
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
                departmentSelect.value = currentDepartmentId || '';
                if (currentDepartmentId) {
                    loadMunicipalitiesByDepartment(currentDepartmentId, currentMunicipalityId);
                } else {
                    municipioSelect.innerHTML = '<option value="">Seleccione un departamento primero</option>';
                }
            } else if (countryId && countryId !== elSalvadorId) {
                departmentSelect.disabled = true;
                departmentSelect.value = '';
                municipioSelect.innerHTML = '<option value="">Seleccione un país válido</option>';
                municipioSelect.disabled = true;
            } else {
                departmentSelect.disabled = false;
                municipioSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
            }
        });

        departmentSelect.addEventListener('change', function () {
            let deptId = this.value;
            loadMunicipalitiesByDepartment(deptId, currentMunicipalityId);
        });

        // Inicializar si el país es El Salvador
        if (countrySelect.value && parseInt(countrySelect.value) === elSalvadorId) {
            if (currentDepartmentId) {
                loadMunicipalitiesByDepartment(currentDepartmentId, currentMunicipalityId);
            }
        }

        // ============================================
        // LÓGICA PARA NIT: TODOS LOS CAMPOS OBLIGATORIOS EXCEPTO NOMBRE DE EMPRESA
        // ============================================
        const documentTypeSelect = document.getElementById('document');
        const nrcInput = document.getElementById('nrc');
        const activitySearch = document.getElementById('activity_search');
        const activityHidden = document.getElementById('activity_id');
        const documentNumberInput = document.getElementById('document_number');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const addressInput = document.getElementById('address');
        
        // Labels para agregar el indicador visual
        const nrcLabel = document.getElementById('nrcLabel');
        const activityLabel = document.getElementById('activityLabel');
        const emailLabel = document.getElementById('emailLabel');
        const phoneLabel = document.getElementById('phoneLabel');
        const addressLabel = document.getElementById('addressLabel');
        const documentNumberLabel = document.getElementById('documentNumberLabel');
        const departmentLabel = document.getElementById('departmentLabel');
        const municipalityLabel = document.getElementById('municipalityLabel');

        function updateRequiredFields() {
            const isNIT = documentTypeSelect.value === '36';
            
            // Lista de campos a hacer obligatorios cuando es NIT
            const fieldsToValidate = [
                { element: nrcInput, label: nrcLabel, name: 'nrc' },
                { element: activitySearch, label: activityLabel, name: 'activity_search' },
                { element: activityHidden, label: null, name: 'activity_id' },
                { element: documentNumberInput, label: documentNumberLabel, name: 'document_number' },
                { element: emailInput, label: emailLabel, name: 'email' },
                { element: phoneInput, label: phoneLabel, name: 'phone' },
                { element: addressInput, label: addressLabel, name: 'address' }
            ];
            
            if (isNIT) {
                // Marcar todos los campos como obligatorios
                fieldsToValidate.forEach(field => {
                    if (field.element) {
                        field.element.setAttribute('required', 'required');
                    }
                    if (field.label && !field.label.classList.contains('required-indicator')) {
                        field.label.classList.add('required-indicator');
                    }
                });
                
                // También hacer obligatorios departamento y municipio (si El Salvador está seleccionado)
                if (parseInt(countrySelect.value) === elSalvadorId) {
                    departmentSelect.setAttribute('required', 'required');
                    municipioSelect.setAttribute('required', 'required');
                    if (departmentLabel) departmentLabel.classList.add('required-indicator');
                    if (municipalityLabel) municipalityLabel.classList.add('required-indicator');
                }
                
            } else {
                // Remover required de todos los campos
                fieldsToValidate.forEach(field => {
                    if (field.element) {
                        field.element.removeAttribute('required');
                    }
                    if (field.label) {
                        field.label.classList.remove('required-indicator');
                    }
                });
                
                // Remover required de departamento y municipio
                departmentSelect.removeAttribute('required');
                municipioSelect.removeAttribute('required');
                if (departmentLabel) departmentLabel.classList.remove('required-indicator');
                if (municipalityLabel) municipalityLabel.classList.remove('required-indicator');
            }
        }
        
        // Escuchar cambios en el tipo de documento
        documentTypeSelect.addEventListener('change', updateRequiredFields);
        
        // También cuando cambie el país, actualizar requeridos de departamento/municipio si es NIT
        countrySelect.addEventListener('change', function() {
            const isNIT = documentTypeSelect.value === '36';
            const isElSalvador = parseInt(this.value) === elSalvadorId;
            
            if (isNIT && isElSalvador) {
                departmentSelect.setAttribute('required', 'required');
                municipioSelect.setAttribute('required', 'required');
                if (departmentLabel) departmentLabel.classList.add('required-indicator');
                if (municipalityLabel) municipalityLabel.classList.add('required-indicator');
            } else if (isNIT && !isElSalvador) {
                departmentSelect.removeAttribute('required');
                municipioSelect.removeAttribute('required');
                if (departmentLabel) departmentLabel.classList.remove('required-indicator');
                if (municipalityLabel) municipalityLabel.classList.remove('required-indicator');
                
                // Limpiar valores si no es El Salvador
                departmentSelect.value = '';
                municipioSelect.innerHTML = '<option value="">Seleccionar municipio…</option>';
            } else if (!isNIT) {
                departmentSelect.removeAttribute('required');
                municipioSelect.removeAttribute('required');
                if (departmentLabel) departmentLabel.classList.remove('required-indicator');
                if (municipalityLabel) municipalityLabel.classList.remove('required-indicator');
            }
        });
        
        // Inicializar la función al cargar la página
        updateRequiredFields();
        
        // Asegurar que el campo nombre siempre tenga el indicador visual
        const nameLabel = document.querySelector('label[for="name"]');
        if (nameLabel && !nameLabel.classList.contains('required-star')) {
            nameLabel.classList.add('required-star');
        }
        
        // Asegurar que el campo país siempre tenga el indicador visual
        const countryLabel = document.querySelector('label[for="country_id"]');
        if (countryLabel && !countryLabel.classList.contains('required-indicator')) {
            countryLabel.classList.add('required-indicator');
        }
    </script>
@endpush