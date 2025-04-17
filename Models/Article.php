<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded = [];

    public function Doctor(){
        return $this->belongsTo(ArticleDoctor::class, 'article_doctor_id');
    }

    public function image(){
        return $this->morphOne(Media::class, 'model');
    }
}
