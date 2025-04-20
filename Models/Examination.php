<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\MediaUrlTrait;

class Examination extends Model implements HasMedia
{
    use HasFactory , InteractsWithMedia, MediaUrlTrait;

    protected $table = 'examinations';
    protected $fillable = ['title','description','section_id'];

    public function Section(){
        return $this->belongsTo(Section::class);
    }
    
    public function details(){
        return $this->hasMany(ExaminationDetails::class, 'examination_id');
    }
}
