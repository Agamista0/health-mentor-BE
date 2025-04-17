<?php

namespace App\Http\Controllers\Dashboard\Age;

use App\Http\Controllers\Controller;
use App\Models\AgeStatistic;
use Illuminate\Http\Request;

class AgeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ages = AgeStatistic::orderByDesc('id')->get() ;
        return view('admin.age.index', compact('ages'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.age.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'min_age' => 'required|numeric|min:0',
            'max_age' => 'required|numeric|gt:min_age',
            'gender' => 'required|in:male,female',
            'value.*' => 'nullable', // السماح بقيم متعددة لحقل value
            'section_id' => 'array',
            'section_id.*' => 'exists:sections,id',
        ]);

        // تصفية القيم الفارغة (null) من المصفوفة
        $values = array_filter($validatedData['value'], function ($value) {
            return !is_null($value);
        });

        // تأكد من عدم وجود قيم فارغة قبل تخزينها
        if (count($values) > 0) {
            // تخزين القيم بفاصلة بينها
            $valueString = implode(',', $values);

            $ageStatistic = AgeStatistic::create([
                'min_age' => $validatedData['min_age'],
                'max_age' => $validatedData['max_age'],
                'gender' => $validatedData['gender'],
                'value' => $valueString,
                'section_id' => implode(',', $validatedData['section_id']),
            ]);

            return redirect()->route('ages.index')->with('success', __('words.created'));
        } else {
            // إذا لم يكن هناك قيم لتخزينها، فقم بإعادة التوجيه برسالة خطأ
            return redirect()->back()->with('error', __('No values provided'));
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $age = AgeStatistic::find($id);

        return view('admin.age.show', compact('age'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $age = AgeStatistic::findOrFail($id);
        return view('admin.age.edit', compact('age'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    $validatedData = $request->validate([
        'min_age' => 'required|numeric|min:0',
        'max_age' => 'required|numeric|gt:min_age',
        'gender' => 'required|in:male,female',
        'value.*' => 'nullable', // Allow multiple values for the 'value' field
        'section_id' => 'array',
        'section_id.*' => 'exists:sections,id',
    ]);

    // Filter out empty values (null) from the array
    $values = array_filter($validatedData['value'], function ($value) {
        return !is_null($value);
    });

    // Make sure there are no empty values before storing
    if (count($values) > 0) {
        // Store the values separated by commas
        $valueString = implode(',', $values);

        $ageStatistic = AgeStatistic::findOrFail($id);
        $ageStatistic->update([
            'min_age' => $validatedData['min_age'],
            'max_age' => $validatedData['max_age'],
            'gender' => $validatedData['gender'],
            'value' => $valueString,
            'section_id' => implode(',', $validatedData['section_id']),
        ]);

        return redirect()->route('ages.index')->with('success', __('words.updated'));
    } else {
        // If there are no values to store, redirect back with an error message
        return redirect()->back()->with('error', __('No values provided'));
    }
}

    public function destroy(string $id)
    {
        $age = AgeStatistic::find($id);
        $age->delete();
        return redirect()->route('ages.index')->with('success', __('words.deleted'));
    }
}
