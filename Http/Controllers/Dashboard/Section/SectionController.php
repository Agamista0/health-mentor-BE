<?php

namespace App\Http\Controllers\Dashboard\Section;

use App\Http\Controllers\Controller;
use App\Http\Requests\SectionRequest;
use App\Models\Media;
use App\Models\Section;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class SectionController extends Controller
{
    protected $mediaService;

    // public function __construct(MediaService $mediaService)
    // {
    //   $this->mediaService = $mediaService;
    //   $this->middleware('permission:section-list', ['only' => ['index']]);
    //   $this->middleware('permission:section-create', ['only' => ['create','store']]);
    //   $this->middleware('permission:section-edit', ['only' => ['edit','update']]);
    //   $this->middleware('permission:section-delete', ['only' => ['destroy']]);
    // }

    public function index()
    {
        $sections = Section::all();
        return view('admin.section.index', compact('sections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.section.create');
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:svg|max:2048', // SVG file with max size of 2048 KB
            'icon' => 'nullable|file|mimes:svg|max:2048', // SVG file with max size of 2048 KB
        ]);

        try {
            // Create a new Section instance
            $section = new Section();
            $section->name = $validatedData['name'];
            $section->save();

            // Handle image upload
            if ($request->hasFile('image')) {
                $section->addMediaFromRequest('image')->toMediaCollection('images');
            }

            // Handle icon upload
            if ($request->hasFile('icon')) {
                $section->addMediaFromRequest('icon')->toMediaCollection('icons');
            }

            return redirect()->route('section.index')->with('success', __('words.created'));
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->route('section.index')->with('error', __('words.error'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $section = Section::with(['questions'])->where('id', $id)->first();

        return view('admin.section.show', compact('section'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $section = Section::findOrFail($id);

        return view('admin.section.update', compact('section'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // الحصول على القسم المطلوب باستخدام المعرف الممرر
        $section = Section::findOrFail($id);
    
       $request->validate([
        'name' => [
            'required',
            Rule::unique('sections')->ignore($section->id),
        ],
    ]);
    
        $section->update($request->all());
    
        if ($request->hasFile('image')) {
            $section->clearMediaCollection('images');
            $section->addMediaFromRequest('image')->toMediaCollection('images');
        }
    
        if ($request->hasFile('icon')) {
            $section->clearMediaCollection('icons');
            $section->addMediaFromRequest('icon')->toMediaCollection('icons');
        }
    
        return redirect()->route('section.index')->with('success', __('words.updated'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $section = Section::find($id);
        $section->delete();

        return redirect()->route('section.index')->with('success', __('words.deleted'));
    }

    public function activation($id)
    {
        $section = Section::where('id', $id)->first();
        $message = __('words.section_active');

        if ($section->is_active) {
            $section->update(['is_active' => 0]);
            $message = __('words.section_not_active');
        } else
            $section->update(['is_active' => 1]);

        return redirect()->route('section.index')
            ->with('success', $message);
    }
}
