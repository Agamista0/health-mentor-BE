<?php

namespace App\Http\Controllers\Dashboard\MedicalTest;

use App\Http\Controllers\Controller;
use App\Models\MedicalTest;
use App\Models\MedicalTestValue;
use Illuminate\Http\Request;

class MedicalTestValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tests = MedicalTestValue::get();

        return view('admin.medical_test_values.index', compact('tests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medical_tests = MedicalTest::get();

        return view('admin.medical_test_values.create', compact('medical_tests'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
             'min' => 'required|numeric',
            'max' => 'required|numeric',
            'gender' => 'required|integer|min:0|max:1',
            'medical_test_id' => 'required|exists:medical_tests,id',
        ]);
        
        MedicalTestValue::create($request->all());

        return redirect()->route('medical_test_values.index')->with('success', __('words.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $test = MedicalTestValue::find($id);

        return view('admin.medical_test_values.show', compact('test'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $medical_tests = MedicalTest::get();

        $test = MedicalTestValue::find($id);

        return view('admin.medical_test_values.update', compact('medical_tests', 'test'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $validatedData = $request->validate([
            'name' => 'required|string|max:255',
             'min' => 'required|numeric',
            'max' => 'required|numeric',
            'gender' => 'required|integer|min:0|max:1',
            'medical_test_id' => 'required|exists:medical_tests,id',
        ]);
        
        $test = MedicalTestValue::find($id);

        $test->update($request->all());

        return redirect()->route('medical_test_values.index')->with('success' , __('words.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
        $medicalTestValue = MedicalTestValue::findOrFail($id);

        // التحقق من وجود العلاقات المرتبطة في medical_test_value_units
        if ($medicalTestValue->medicalTestValueUnits()->exists()) {
            // حذف العلاقات المرتبطة في medical_test_value_units
            $medicalTestValue->medicalTestValueUnits()->delete();
        }

        $medicalTestValue->delete();

        return redirect()->route('medical_test_values.index')->with('success', __('words.deleted'));
    } catch (Exception $e) {
        // التعامل مع الخطأ
        return $e->getMessage();
    }
    }
}
