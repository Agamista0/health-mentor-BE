<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyStatusDetail extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'health_dynamics' => 'array'
    ];

    public function Section(){
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function result(){
        return $this->belongsTo(Result::class);
    }
}
