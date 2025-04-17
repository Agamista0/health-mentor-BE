<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'multi_choice'];

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_question');
    }

    public function answers(){
        return $this->hasMany(Answer::class, 'question_id');
    }
    
    public function units(){
        return $this->HasMany(Unit::class);
    }
}
