<?php

namespace App\Http\Controllers\Dashboard\MedicalTest;

use App\Http\Controllers\Controller;
use App\Models\Advice;
use App\Models\MedicalTestValue;
use Faker\Provider\Medical;
use Illuminate\Http\Request;

class AdviceController extends Controller
{
    public function index(){
        $advices = Advice::get();

        return view('admin.advice.index', compact('advices'));
    }

    public function create(){
        $medical_tests = MedicalTestValue::get();

        return view('admin.advice.create', compact('medical_tests'));
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'medical_test_id' => 'required|integer',
        ]);
        Advice::create([
            'title' => $request->title,
            'description' => $request->description,
            'medical_test_id' => $request->medical_test_id
        ]);
        return redirect()->route('advices.index')->with('success', __('words.created'));
    }

    public function show($id){
        $advice = Advice::find($id);

        return view('admin.advice.show', compact('advice'));
    }

    public function edit($id){
        $advice = Advice::find($id);

        $medical_tests = MedicalTestValue::get();

        return view('admin.advice.update', compact('advice', 'medical_tests'));
    }

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'medical_test_id' => 'required|integer',
        ]);
        $advice = Advice::find($id);

        $advice->update([
            'title' => $request->title,
            'description' => $request->description,
            'medical_test_id' => $request->medical_test_id
        ]);

        return redirect()->route('advices.index')->with('success', __('words.updated'));
    }

    public function destroy($id){
        $advice = Advice::find($id);

        $advice->delete();

        return redirect()->route('advices.index')->with('success', __('words.deleted'));

    }
}
