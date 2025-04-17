<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalTest extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function Section(){
        return $this->belongsTo(Section::class);
    }

    public function values(){
        return $this->hasMany(MedicalTestValue::class, 'medical_test_id');
    }

    public function icon(){
        return $this->morphOne(Media::class, 'model');
    }

    public function wiki(){
        return $this->hasMany(HealthMentorWiki::class, 'medical_test_id');
    }

    public function units(){
        return $this->belongsTo(ParentUnit::class, 'id', 'medical_test_id');
    }

    public function parentUnit()
    {
        return $this->hasOne(ParentUnit::class);
    }

  	public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
