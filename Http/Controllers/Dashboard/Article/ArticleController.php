<?php

namespace App\Http\Controllers\Dashboard\Article;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleDoctor;
use App\Models\Media;
use App\Models\MedicalTest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::get();

        return view('admin.article.index', compact('articles'));
    }

    public function create()
    {
        $doctors = ArticleDoctor::get();

        return view('admin.article.create', compact('doctors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $articleData = $request->except('image');

            $articleData['premium'] = $request->has('premium');
            $medicalTestId = $request->input('medical_test_id');

            $medicalTest = Article::findOrFail($medicalTestId);

            $articleData['section_id'] = $medicalTest->section_id;
            $article = Article::create($articleData);

            if ($request->hasFile('image')) {
                $image= $request->hasFile('image');

                $article->addMediaFromRequest('image')->toMediaCollection('images');
            }




            return redirect()->route('articles.index')->with('success', __('words.created'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $article = Article::find($id);

        return view('admin.article.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $article = Article::findOrFail($id);

        $doctors = ArticleDoctor::get();

        return view('admin.article.update', compact('article', 'doctors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $article = Article::findOrFail($id);

            $articleData = $request->except('image');

            $articleData['premium'] = $request->has('premium');

            $medicalTestId = $request->input('medical_test_id');
            $medicalTest = Article::findOrFail($medicalTestId);
            $articleData['section_id'] = $medicalTest->section_id;

            $article->update($articleData);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $article->clearMediaCollection('images');

                $article->addMediaFromRequest('image')->toMediaCollection('images');
            }

            return redirect()->route('articles.index')->with('success', __('words.updated'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $article = Article::find($id);
        $article->delete();

        return redirect()->route('articles.index')->with('success', __('words.deleted'));
    }
}
