<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LaboratoryFiles extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            // Delete the file when the record is deleted
            if ($model->file_path) {
                Storage::delete($model->file_path);
            }
        });
    }

    // Define the relationship with LaboratoryTest
    public function laboratoryTest()
    {
        return $this->belongsTo(LaboratoryTests::class);
    }


}
