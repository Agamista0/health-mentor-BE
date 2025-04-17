<?php

namespace App\Http\Controllers\Dashboard\Result;

use App\Http\Controllers\Controller;
use App\Models\Risk;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    public function index(){
        $risks = Risk::get();

        return view('admin.risk.index', compact('risks'));
    }

    public function create(){
        return view('admin.risk.create');
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'details' => 'required|string',
            'upcoming_risks' => 'nullable|string', 
        ]);
        
      //  dd($validatedData);
       $upcomingRisks = $request->has('upcoming_risks') ? $request->upcoming_risks : null;
        Risk::create([
            'title' => $request->title,
            'details' => $request->details,
            'upcoming_risks' =>$upcomingRisks
        ]);

        return redirect()->route('risks.index')->with('success', __('words.created'));
    }
    
    public function show($id){
        $risk = Risk::find($id);

        return view('admin.risk.show', compact('risk'));
    }


    public function edit($id){
        $risk = Risk::find($id);

        return view('admin.risk.update', compact('risk'));
    }

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'details' => 'required|string',
            'upcoming_risks' => 'nullable|string', 
        ]);
        $risk = Risk::find($id);

        $risk->update([
            'title' => $request->title,
            'details' => $request->details,
            'upcoming_risks' => $request->upcoming_risks
        ]);

        return redirect()->route('risks.index')->with('success', __('words.updated'));
    }
}
