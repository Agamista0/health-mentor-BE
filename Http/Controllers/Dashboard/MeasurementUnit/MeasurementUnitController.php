<?php

namespace App\Http\Controllers\Dashboard\MeasurementUnit;

use App\Http\Controllers\Controller;
use App\Models\MeasurementUnits;
use Illuminate\Http\Request;

class MeasurementUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $measurements = MeasurementUnits::all();
        return view('admin.measurement.index',compact('measurements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.measurement.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $measurement = new MeasurementUnits();
        $measurement->name = $validatedData['name'];;
        $measurement->save();
        return redirect()->route('measurement.index')->with('success', __('words.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $measurement = MeasurementUnits::findOrFail($id);
        return view('admin.measurement.show',compact('measurement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $measurement = MeasurementUnits::findOrFail($id);
        return view('admin.measurement.edit',compact('measurement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',

            ]);
            $measurement = MeasurementUnits::findOrFail($id);
            $measurement->name = $validatedData['name'];
            $measurement->save();

            return redirect()->route('measurement.index')->with('success', __('words.updated'));
        } catch (\Exception $e) {
            return $e->getMessage(); // You can handle the exception as per your application's error handling strategy
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $measurement = MeasurementUnits::findOrFail($id);
        $measurement->delete();
        return redirect()->route('measurement.index')->with('success', __('words.deleted'));
    }
}
