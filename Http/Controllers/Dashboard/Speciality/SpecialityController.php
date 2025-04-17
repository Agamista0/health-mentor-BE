<?php

namespace App\Http\Controllers\Dashboard\Speciality;

use App\Http\Controllers\Controller;
use App\Models\Speciality;
use Illuminate\Http\Request;

class SpecialityController extends Controller
{
    public function index(){
        $specialities = Speciality::get();

        return view('admin.speciality.index', compact('specialities'));
    }

    public function create(){
        return view('admin.speciality.create');
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        Speciality::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
        return redirect()->route('specialities.index')->with('success', __('words.created'));
    }

    public function show($id){
        $speciality = Speciality::find($id);
        return view('admin.speciality.show', compact('speciality'));
    }

    public function edit($id){
        $speciality = Speciality::find($id);

        return view('admin.speciality.update', compact('speciality'));
    }

    public function update($id, Request $request){
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        $speciality = Speciality::find($id);

        $speciality->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('specialities.index')->with('success', __('words.updated'));
    }

    public function destroy($id){
        $speciality = Speciality::find($id);

        $speciality->delete();

        return redirect()->route('specialities.index')->with('success', __('words.deleted'));

    }
}
