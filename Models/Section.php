<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Section extends Model implements HasMedia
{
    use HasFactory , InteractsWithMedia;
    protected $fillable = ['name', 'is_active'];
    public function questions(){
        return $this->belongsToMany(Question::class, 'section_question');
    }
    public function Articles(){
        return $this->hasMany(Article::class);
    }
    public function ageStatistics()
    {
        return $this->belongsToMany(AgeStatistic::class);
    }
}
