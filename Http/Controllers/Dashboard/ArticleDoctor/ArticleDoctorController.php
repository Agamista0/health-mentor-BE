<?php

namespace App\Http\Controllers\Dashboard\ArticleDoctor;

use App\Http\Controllers\Controller;
use App\Models\ArticleDoctor;
use App\Models\Speciality;

use Illuminate\Http\Request;

class ArticleDoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = ArticleDoctor::get();

        return view('admin.article_doctor.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $specialities = Speciality::get();
        return view('admin.article_doctor.create', compact('specialities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
         $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'name' => 'required|string',
            'speciality_id' => 'required|exists:specialities,id',
        ]);
        ArticleDoctor::create($request->all());

        return redirect()->route('doctors.index')->with('success', __('words.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $doctor = ArticleDoctor::find($id);

        return view('admin.article_doctor.show', compact('doctor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $specialities = Speciality::get();

        $doctor = ArticleDoctor::find($id);

        return view('admin.article_doctor.update', compact('doctor', 'specialities'));
    }

    /**
     * Update the specified resource in storage.
     */
  public function update(Request $request, $id)
    {
    try {
        // التحقق من البيانات المُرسلة
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'name' => 'required|string',
            'speciality_id' => 'required|exists:specialities,id',
        ]);

        // البحث عن السجل المراد تحديثه
        $doctor = ArticleDoctor::findOrFail($id);

        // تحديث البيانات
        $doctor->update($validatedData);

        return redirect()->route('doctors-article.index')->with('success', __('words.updated'));
    } catch (\Exception $e) {
        // التعامل مع الأخطاء العامة
       return $e->getMessage();
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doctor = ArticleDoctor::where('id',$id)->delete();
        return redirect()->route('doctors-article.index')->with('success', __('words.deleted'));
    }
}
