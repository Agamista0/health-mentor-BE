<?php

namespace App\Http\Controllers\Dashboard\SubUnit;

use App\Http\Controllers\Controller;
use App\Models\SubUnit;
use Illuminate\Http\Request;

class SubUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subs = SubUnit::all();
        return view('admin.sub.index',compact('subs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.sub.create');
    }

    /**
     * Store a newly created resource in storage.
     */
 public function store(Request $request)
{
    $validatedData = $request->validate([
       // 'medical_test_id' => $request->type == 'VitalSigns' ? 'required|exists:medical_tests,id' : '',
        'sub_unit' => 'required|string|max:255',
        'convert_unit' => 'required|numeric',
       'parent_units_id' => 'required|exists:parent_units,id',
    ]);

    $sub = new SubUnit();
    $sub->sub_unit = $validatedData['sub_unit'];
   $sub->parent_units_id = $validatedData['parent_units_id'];
    $sub->convert_unit = $validatedData['convert_unit'];
   // $sub->medical_test_id = $validatedData['medical_test_id'];
    $sub->save();

    return redirect()->route('sub.index')->with('success', __('words.created'));
}
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sub = SubUnit::findOrFail($id);
        return view('admin.sub.show',compact('sub'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sub = SubUnit::findOrFail($id);
        return view('admin.sub.edit',compact('sub'));
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, $id)
    {
        // dd($request->all());
        try {
            $validatedData = $request->validate([
                'medical_test_id' => $request->type == 'VitalSigns' ? 'required|exists:medical_tests,id' : '',
                'sub_unit' => 'required|string|max:255',
                'convert_unit' => 'required|numeric',
               'parent_units_id' => 'required|exists:parent_units,id',
            ]);
            $sub = SubUnit::findOrFail($id);
            $sub->sub_unit = $validatedData['sub_unit'];;
            $sub->parent_units_id = $validatedData['parent_units_id'];
            $sub->convert_unit = $validatedData['convert_unit'];
            $sub->medical_test_id = $validatedData['medical_test_id'];
            $sub->save();
            return redirect()->route('sub.index')->with('success', __('words.updated'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sub = SubUnit::findOrFail($id);
        $sub->delete();
        return redirect()->route('sub.index')->with('success', __('words.deleted'));

    }
}
