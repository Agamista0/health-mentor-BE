<?php

namespace App\Http\Controllers\Dashboard\Question;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Answer;
use App\Models\Media;
use App\Models\Question;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class QuestionController extends Controller
{

    // public function __construct()
    // {
    //   $this->middleware('permission:question-list', ['only' => ['index']]);
    //   $this->middleware('permission:question-create', ['only' => ['create','store']]);
    //   $this->middleware('permission:question-edit', ['only' => ['edit','update']]);
    //   $this->middleware('permission:question-delete', ['only' => ['destroy']]);
    // }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $questions = Question::all();

        return view('admin.question.index', compact('questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = Section::all();

        return view('admin.question.create', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuestionRequest $request)
    {
        $question = Question::create([
            'name' => $request->question,
            'description' => $request->description,
            'type' => isset($request->multi_choice) ?1 : 0,
        ]);

        $question->sections()->attach($request->section_id);

        foreach ($request->answer as $answerData) {
            $answer = Answer::create([
                'name' => $answerData['name'],
                'description' => $answerData['description'],
                'question_id' => $question->id,
            ]);
            $answer->save();

                if ($request->hasFile('image')) {
                    $answer->clearMediaCollection('images');
                    $answer->addMediaFromRequest('image')->toMediaCollection('images');
                }
            }
        return redirect()->route('question.index')->with('success', __('words.created'));
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $question = Question::with(['answers','sections'])->where('id', $id)->first();

        return view('admin.question.show', compact('question'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sections = Section::all();

        $question = Question::findOrFail($id);

        return view('admin.question.update', compact('sections', 'question'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuestionRequest $request, string $id)
    {
        $question = Question::findOrFail($id);

        $question->update([
            'name' => $request->question,
            'description' => $request->description,
            'type' => isset($request->multi_choice) ?1 : 0,
        ]);

        $question->sections()->sync($request->section_id);

        foreach ($request->answer as $answerData) {
            $answer = Answer::updateOrCreate(
                ['id' => $answerData['id'] ?? null],
                [
                    'name' => $answerData['name'],
                    'description' => $answerData['description'],
                    'question_id' => $question->id,
                ]
            );
            if ($request->hasFile('image')) {
                $answer->clearMediaCollection('images');
                $answer->addMediaFromRequest('image')->toMediaCollection('images');
            }
        }

        return redirect()->route('question.index')->with('success', __('words.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $question = Question::findOrFail($id);

        $question->answers->each(function ($answer) {
            $answer->icon()->delete();
        });

        $question->delete();

        return redirect()->route('question.index')->with('success', __('words.deleted'));
    }
}
