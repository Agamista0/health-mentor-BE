<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentUnit extends Model
{
    use HasFactory;
   protected $guarded = [];
    public $timestamps = false;
    public function medicalTest()
    {
        return $this->belongsTo(MedicalTest::class, 'medical_test_id');
    }
    public function subUnits()
    {
        return $this->hasMany(SubUnit::class,'parent_units_id');
    }

}
