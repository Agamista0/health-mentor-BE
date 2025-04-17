<?php
namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Str;

class MediaService
{
    // public function upload($data, $folder)
    // {
    //     // $request->validate([
    //     //     'file' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048',
    //     // ]);

    //     $file = $data->file('file');
    //     $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

    //     $path = $file->storeAs($folder, $fileName);

    //     return response()->json(['message' => 'File uploaded successfully', 'filename' => $fileName]);
    // }

    // public function update(Request $request, $filename)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048',
    //     ]);

    //     $file = $request->file('file');
    //     $filePath = 'uploads/' . $filename;

    //     if (Storage::exists($filePath)) {
    //         Storage::delete($filePath);
    //     }

    //     $newFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
    //     $newPath = $file->storeAs('uploads', $newFileName);

    //     return response()->json(['message' => 'File updated successfully', 'filename' => $newFileName]);
    // }
    public function createMedia($model, $image, $path)
    {
      return Media::create([
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'uuid' => (string) Str::uuid(),
            'collection_name' => 'images',
            'name' => $image->getClientOriginalName(),
            'file_name' => $path,
            'mime_type' => $image->getClientMimeType(),
            'disk' => 'public',
            'size' => $image->getSize(),
        ]);
        dd($media);
    }

}
