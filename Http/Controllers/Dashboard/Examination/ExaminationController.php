<?php

namespace App\Http\Controllers\Dashboard\Examination;

use App\Http\Controllers\Controller;
use App\Models\Examination;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $examinations = Examination::all();

        return view('admin.examination.index', compact('examinations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.examination.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'section_id' => 'required|array',
            'section_id.*' => 'exists:sections,id',
        ]);

        $examination = new Examination();
        $examination->title = $validatedData['title'];
        $examination->description = $validatedData['description'];
        $examination->section_id = implode(',', $validatedData['section_id']);
        $examination->save();
        if ($request->hasFile('image')) {
            $image= $request->hasFile('image');

            $examination->addMediaFromRequest('image')->toMediaCollection('images');
        }
        return redirect()->route('examinations.index')->with('success', __('words.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $examination = Examination::findOrFail($id);

        return view('admin.examination.show',compact('examination'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $examination = Examination::findOrFail($id);

        return view('admin.examination.edit',compact('examination'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $examination = Examination::find($id);
        $examination->update($request->all());
        if ($request->hasFile('image')) {
            $examination->clearMediaCollection('images');
            $examination->addMediaFromRequest('image')->toMediaCollection('images');
        }
        return redirect()->route('examinations.index')->with('success', __('words.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $examination = Examination::findOrFail($id);
        $examination->delete();
        return redirect()->route('examinations.index')->with('success', __('words.delete'));
    }
}
