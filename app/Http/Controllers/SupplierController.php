<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Municipality;
use App\Models\EconomicActivity;
use App\Models\Department;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::with(['country', 'municipality', 'economicActivity'])->get();
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::all();
        $economicActivities = EconomicActivity::all();
        $municipalities = Municipality::all();
        $departments = Department::all();

        return view('suppliers.create', compact('countries', 'economicActivities', 'municipalities', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'document_number' => 'nullable|string|unique:suppliers|max:255',
            'email' => 'nullable|email|unique:suppliers',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
            'activity_id' => 'nullable|exists:economic_activities,id',
        ]);

        Supplier::create($validatedData);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        $countries = Country::all();
        $economicActivities = EconomicActivity::all();
        $municipalities = Municipality::all();
        $departments = Department::all();

        return view('suppliers.edit', compact('supplier', 'countries', 'economicActivities', 'municipalities', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'document_number' => 'nullable|string|unique:suppliers,document_number,' . $supplier->id . '|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'municipality_id' => 'nullable|exists:municipalities,id',
            'activity_id' => 'nullable|exists:economic_activities,id',
        ]);

        $supplier->update($validatedData);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        //
    }
}
