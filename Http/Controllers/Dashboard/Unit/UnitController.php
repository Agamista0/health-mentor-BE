<?php

namespace App\Http\Controllers\Dashboard\Unit;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::all();
        return view('admin.unit.index',compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.unit.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|in:Question,VitalSigns',
                'parent_units_id' => 'nullable|exists:parent_units,id',
                'question_id' => $request->type == 'Question' ? 'required|exists:questions,id' : '',
                'medical_test_id' => $request->type == 'VitalSigns' ? 'required|exists:medical_tests,id' : '',
            ]);

            $unit = new Unit();
            $unit->title = $validatedData['title'];
            $unit->type = $validatedData['type'];
            $unit->parent_units_id = $validatedData['parent_units_id']??null;

            if ($request->type == 'Question') {
                $unit->question_id = $validatedData['question_id'];
            } elseif ($request->type == 'VitalSigns') {
                $unit->medical_test_id = $validatedData['medical_test_id'];
            }
            $unit->save();
            return redirect()->route('units.index')->with('success', __('words.created'));
//        } catch (\Exception $e) {
//            return $e->getMessage(); // You can handle the exception as per your application's error handling strategy
//        }
    }

    public function show(string $id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.unit.show',compact('unit'));
    }
    public function edit(string $id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.unit.edit',compact('unit'));
    }


    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required',
                'question_id' => 'required|integer',
                'question_id.*' => 'exists:questions,id',
            ]);

            $unit = Unit::findOrFail($id);

            $unit->update([
                'title' => $validatedData['title'],
                'type' => $validatedData['type'],
                'question_id' => $validatedData['question_id'],
            ]);

            return redirect()->route('units.index')->with('success', __('words.updated'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function destroy(string $id)
    {
//        $unit = unit::findOrFail($id);
//        $unit->delete();
//        return redirect()->route('units.index')->with('success', __('words.delete'));
    }
}
