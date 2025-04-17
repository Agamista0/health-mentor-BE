<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['title','type','question_id'];
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    public function parentUnit()
    {
        return $this->belongsTo(ParentUnit::class,'parent_units_id');
    }
    public function medicalTest(){
        return $this->belongsTo(MedicalTest::class);
    }
}
