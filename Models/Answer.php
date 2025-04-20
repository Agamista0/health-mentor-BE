<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\MediaUrlTrait;

class Answer extends Model implements HasMedia
{
    use HasFactory , InteractsWithMedia, MediaUrlTrait;
    protected $fillable =[
        'name',
        'description',
        'question_id',
    ];

    public function Question(){
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function icon(){
        return $this->morphOne(Media::class, 'model');
    }
}
