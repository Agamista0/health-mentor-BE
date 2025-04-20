<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AleafiaAssessment extends Model
{
    protected $fillable = [
        'user_id',
        'total_score',
        'category_scores',
        'answers'
    ];

    protected $casts = [
        'category_scores' => 'array',
        'answers' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(AleafiaQuestion::class);
    }
} 