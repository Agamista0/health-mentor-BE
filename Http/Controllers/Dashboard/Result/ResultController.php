<?php

namespace App\Http\Controllers\Dashboard\Result;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Risk;
use App\Models\Section;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(){
        $results = Result::get();

        return view('admin.result.index', compact('results'));
    }
    public function create(){
        $sections = Section::get();

        $risks = Risk::get();

        return view('admin.result.create', compact('sections', 'risks'));
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'section' => 'required|integer',
            'known_issues.*' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'risk_id' => 'required|integer', 
            'status' => 'required|string|max:255',
            'value' => 'required', 
        ]);
        $result = Result::create([
            'section_id' => $request->section,
            'known_issues' => $request->known_issues,
            'title' => $request->title,
            'description'   => $request->description,
            'risk_id' => $request->risk_id,
            'status' => $request->status,
            'value' => $request->value,
        ]);

        foreach ($request->answers as $answerId) {
            $result->answers()->attach($answerId);
        }

        return redirect()->route('results.index')->with('success', __('words.created'));
    }
    public function show($id){
        $result = Result::find($id);

        return view('admin.result.show', compact('result'));
    }
    public function edit($id){
        $sections = Section::get();

        $risks = Risk::get();

        $result = Result::find($id);

        return view('admin.result.update', compact('sections', 'result', 'risks'));
    }

    public function update(Request $request, $id){
         try {
         $validatedData = $request->validate([
            'section' => 'required|integer',
            'known_issues.0' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'risk_id' => 'required|integer', 
          //  'status' => 'required|string|max:255',
           // 'value' => 'required', 
        ]);
      
        $result = Result::findOrFail($id);
        $result->section_id = $request->section;
        $result->known_issues = $request->known_issues;
        $result->title = $request->title;
        $result->description = $request->description;
    
        $result->risk_id = $request->risk_id;
    
        $result->save();
    
        $result->answers()->detach();
    
        foreach ($request->answers as $answerId) {
            $result->answers()->attach($answerId);
        }
        
    
        return redirect()->route('results.index')->with('success', __('words.updated'));
         } catch (\Exception $e) {
        return $e->getMessage();
    }
        
    }
    

    public function getQuestions($sectionId) {
        $section = Section::find($sectionId);
        if (!$section) {
            return response()->json(['error' => 'Section not found'], 404);
        }
    
        $questions = $section->questions;
    
        return view('admin.result.partial', compact('questions'));
    }
    
}
