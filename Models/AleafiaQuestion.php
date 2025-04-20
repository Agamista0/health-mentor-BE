<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AleafiaQuestion extends Model
{
    protected $fillable = [
        'category',
        'icon',
        'question_ar',
        'answer_options'
    ];

    protected $casts = [
        'answer_options' => 'array'
    ];

    public function assessments()
    {
        return $this->hasMany(AleafiaAssessment::class);
    }
} 