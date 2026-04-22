<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Department;
use App\Models\EconomicActivity;
use App\Models\Municipality;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['economicActivity', 'municipality.department', 'country']);

        // Búsqueda general
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('nrc', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo de documento
        if ($request->filled('document_type')) {
            $query->where('document', $request->document_type);
        }

        // Filtro por retención de IVA
        if ($request->filled('retains_iva')) {
            $query->where('retains_iva', $request->retains_iva);
        }

        // Paginación
        $perPage = $request->input('per_page', 10);
        $customers = $query->paginate($perPage);

        // Si es una petición AJAX, devolver solo la tabla
        if ($request->ajax()) {
            return view('customers.partials.table', compact('customers'))->render();
        }

        // Datos para los selects (si los necesitas en la vista principal)
        $economicActivities = EconomicActivity::orderBy('description')->get();
        $countries = Country::all();
        $departments = Department::all();
        $municipalities = Municipality::with('department')->get();

        return view('customers.index', compact('customers', 'economicActivities', 'countries', 'departments', 'municipalities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $countries = Country::all();
        $economicActivities = EconomicActivity::all();
        $municipalities = Municipality::all();

        return view('customers.create', compact('departments', 'countries', 'economicActivities', 'municipalities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        $validated = $request->validated();

        $validated['retains_iva'] = $request->has('retains_iva');
        $countryId = $request->country_id;
        $elSalvador = Country::where('name', 'El Salvador')->first();

        if ($countryId != $elSalvador->id) {
            $defaultMunicipality = Municipality::where('department_id', 1)->first();
            if ($defaultMunicipality) {
                $validated['municipality_id'] = $defaultMunicipality->id;
            }
        }

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer) {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $departments = Department::all();
        $countries = Country::all();
        $economicActivities = EconomicActivity::all();
        $municipalities = Municipality::all();

        return view('customers.edit', compact('customer', 'departments', 'countries', 'economicActivities', 'municipalities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        $validated = $request->validated();

        $validated['retains_iva'] = $request->has('retains_iva');

        // Si el país NO es El Salvador, forzar municipality_id = 1 (o el que corresponda)
        $countryId = $request->country_id;
        $elSalvador = Country::where('name', 'El Salvador')->first();

        if ($countryId != $elSalvador->id) {
            // Buscar el primer municipio disponible (ID 1 o el que tenga department_id=1)
            $defaultMunicipality = Municipality::where('department_id', 1)->first();
            if ($defaultMunicipality) {
                $validated['municipality_id'] = $defaultMunicipality->id;
            }
        }

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
