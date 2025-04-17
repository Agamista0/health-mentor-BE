<?php

namespace App\Http\Controllers\Dashboard\ExaminationDetails;

use App\Http\Controllers\Controller;
use App\Models\ExaminationDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExaminationDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $examinationDetails = ExaminationDetails::all();
        return view('admin.examination_details.index',compact('examinationDetails'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.examination_details.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'about' => 'required|string',
            'examination_id' => 'required|array',
            'examination_id.*' => 'exists:examinations,id',
        ]);

        $examination = new ExaminationDetails();
        $examination->title = $validatedData['title'];
        $examination->about = $validatedData['about'];
        $examination->examination_id  =implode( ',', $validatedData['examination_id']);
        $examination->save();
        return redirect()->route('examination_details.index')->with('success', __('words.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $examinationDetails = ExaminationDetails::findOrFail($id);
        return view('admin.examination_details.show',compact('examinationDetails'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $examinationDetails = ExaminationDetails::findOrFail($id);
        return view('admin.examination_details.edit',compact('examinationDetails'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $examinationDetails = ExaminationDetails::find($id);
        $examinationDetails->update($request->all());
        return redirect()->route('examination_details.index')->with('success', __('words.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $examinationDetails = ExaminationDetails::findOrFail($id);
        $examinationDetails->delete();
        return redirect()->route('examination_details.index')->with('success', __('words.delete'));
    }
}
