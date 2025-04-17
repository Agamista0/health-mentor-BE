<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgeStatistic extends Model
{
    use HasFactory;
    protected $table = 'age_statistics' ;

    protected $fillable = [
        'min_age',
        'max_age',
        'value',
        'gender',
        'section_id',
        'medical_test_id'
    ];
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

}
