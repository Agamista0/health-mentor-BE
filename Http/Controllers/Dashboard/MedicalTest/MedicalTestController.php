<?php

namespace App\Http\Controllers\Dashboard\MedicalTest;

use App\Http\Controllers\Controller;
use App\Models\MedicalTest;
use App\Models\Section;
use Illuminate\Http\Request;
use Exception;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Database\QueryException;

class MedicalTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tests = MedicalTest::all();

        return view('admin.medical_test.index', compact('tests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = Section::get();
        
        return view('admin.medical_test.create', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'section_id.*' => 'required|exists:sections,id',
        ]);
        MedicalTest::create($request->all());

        // $test->icon()->save();

        return redirect()->route('medical_tests.index')->with('success', __('words.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $test = MedicalTest::with('Section')->find($id);

        return view('admin.medical_test.show', compact('test'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sections = Section::get();

        $test = MedicalTest::find($id);

        return view('admin.medical_test.update', compact('sections', 'test'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
          $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'section_id.*' => 'required|exists:sections,id',
        ]);
        $test = MedicalTest::find($id);

        $test->update($request->all());

        return redirect()->route('medical_tests.index')->with('success', __('words.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */


public function destroy(string $id)
{
    try {
        // التحقق من وجود العنصر المراد حذفه
        $medicalTest = MedicalTest::findOrFail($id);

        // التحقق من وجود العلاقات المرتبطة في health_mentor_wikis
        if ($medicalTest->wiki()->exists()) {
            // حذف العلاقات المرتبطة في health_mentor_wikis
            $medicalTest->wiki()->delete();
        }

        // الحذف من medical_tests
        $medicalTest->delete();

        return redirect()->route('medical_tests.index')->with('success', __('words.deleted'));
    } catch (\Exception $e) {
        // التعامل مع الخطأ
        return $e->getMessage();
    }
}
}
