<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Customer;
use App\Models\Department;
use App\Models\EconomicActivity;
use App\Models\Municipality;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerRequest;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::with(['country', 'municipality', 'economicActivity'])->orderBy('name')->paginate(5);

        return view('customers.index', compact('customers'));
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
    public function show(Customer $customer)
    {
        
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
