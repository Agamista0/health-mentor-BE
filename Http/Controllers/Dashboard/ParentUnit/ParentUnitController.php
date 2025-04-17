<?php

namespace App\Http\Controllers\Dashboard\ParentUnit;

use App\Http\Controllers\Controller;
use App\Models\ParentUnit;
use App\Models\SubUnit;
use Illuminate\Http\Request;

class ParentUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parents = ParentUnit::all();
		$subUnits = SubUnit::all();
        return view('admin.parent.index',compact('parents','subUnits'));
    }
    public function create()
    {
        return view('admin.parent.create');
    }

    /**
     * Store a newly created resource in storage.
     */
//    public function store(Request $request)
//    {
//        $validatedData = $request->validate([
//            'medical_test_id' => $request->type == 'VitalSigns' ? 'required|exists:medical_tests,id' : '',
//            'parent_unit' => 'required|string|max:255',
//            'convert_unit' => 'required|numeric',
//        ]);
//        $parent = new ParentUnit();
//        $parent->parent_unit = $validatedData['parent_unit'];;
//        $parent->convert_unit = $validatedData['convert_unit'];
//        $parent->medical_test_id = $validatedData['medical_test_id'];
//
//        $parent->save();
//        return redirect()->route('parents.index')->with('success', __('words.created'));
//    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'medical_test_id' => $request->type == 'VitalSigns' ? 'required|exists:medical_tests,id' : '',
            'parent_unit' => 'required|string',
        ]);
        $parentUnit = ParentUnit::create([
            'medical_test_id' => $validatedData['medical_test_id'],
            'parent_unit' => $validatedData['parent_unit'],
        ]);
        $parentId = $parentUnit->id;
        $medical_test = $parentUnit->medical_test_id;
            $subUnit = SubUnit::create([
                'parent_units_id' => $parentId,
                'sub_unit' => $request->sub_unit,
                'convert_unit' => $request->convert_unit,
                'medical_test_id'=> $medical_test,
            ]);

            return redirect()->route('parents.index')->with('success', __('words.created'));
    }
    public function show($id)
    {
        $parentUnit = ParentUnit::findOrFail($id);
        $subUnit = SubUnit::where('parent_units_id', $id)->firstOrFail();

        return view('admin.parent.show', compact('parentUnit', 'subUnit'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $parentUnit = ParentUnit::findOrFail($id);
        $subUnit = SubUnit::where('parent_units_id', $id)->get();
        return view('admin.parent.edit', compact('parentUnit','subUnit' ));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'medical_test_id' => $request->type == 'VitalSigns' ? 'required|exists:medical_tests,id' : '',
            'parent_unit' => 'required|string',
        ]);

        // Find the parent unit by ID
        $parentUnit = ParentUnit::findOrFail($id);

        // Update the attributes of the parent unit
        $parentUnit->update([
            'medical_test_id' => $validatedData['medical_test_id'],
            'parent_unit' => $validatedData['parent_unit'],
        ]);

        // Update or create the sub unit
        $subUnit = SubUnit::updateOrCreate(
            ['parent_units_id' => $id],
            [
                'sub_unit' => isset($request->sub_unit[0]) ? $request->sub_unit[0] : null,
                'convert_unit' => isset($request->convert_unit[0]) ? $request->convert_unit[0] : null,
                'medical_test_id'=> $parentUnit->medical_test_id,
            ]
        );

        return redirect()->route('parents.index')->with('success', __('words.updated'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy ($id)
    {
       $parentUnit = ParentUnit::findOrFail($id);

    SubUnit::where('parent_units_id', $id)->delete();

    $parentUnit->delete();

        return redirect()->route('parents.index')->with('success', __('words.deleted'));
    }
}
