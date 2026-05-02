<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Department;
use App\Models\EconomicActivity;
use App\Models\Municipality;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Supplier::query()->with(['economicActivity', 'municipality.department']);

        // 🔍 Filtro por búsqueda (nombre o documento)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('document_number', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // 🏢 Filtro por departamento (a través del municipio)
        if ($request->filled('department_id')) {
            $query->whereHas('municipality', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // 📍 Filtro por municipio
        if ($request->filled('municipality_id')) {
            $query->where('municipality_id', $request->municipality_id);
        }

        // 💼 Filtro por actividad económica (opcional, buen extra)
        if ($request->filled('activity_id')) {
            $query->where('activity_id', $request->activity_id);
        }

        // 📄 Paginación (15 por página)
        $suppliers = $query->orderBy('name')->paginate(15);

        // Si es petición AJAX, devolver JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($suppliers);
        }

        // Vista normal con datos para los filtros
        $departments = Department::with('municipalities')->get();
        $activities = EconomicActivity::orderBy('description')->get();

        return view('suppliers.index', compact('suppliers', 'departments', 'activities'));
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
        return view('suppliers.show', compact('supplier'));
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
            'document_number' => 'nullable|string|unique:suppliers,document_number,'.$supplier->id.'|max:255',
            'email' => 'nullable|email|unique:suppliers,email,'.$supplier->id,
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
        // Verificar si tiene productos relacionados
        if ($supplier->products()->exists()) {
            $message = 'No se puede eliminar el proveedor porque tiene productos registrados.';


            return redirect()
                ->route('suppliers.index')
                ->with('error', $message);
        }

        $supplier->delete();

        $message = 'Proveedor eliminado correctamente.';

        

        return redirect()
            ->route('suppliers.index')
            ->with('success', $message);
    }
}
