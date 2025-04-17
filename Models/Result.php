<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    protected $table = 'results', $guarded = [], $casts = [
        'known_issues' => 'array'
    ];

    public function answers()
    {
        return $this->belongsToMany(Answer::class);
    }

    public function risk(){
        return $this->belongsTo(Risk::class);
    }

    public function Section(){
        return $this->belongsTo(Section::class);
    }
}
