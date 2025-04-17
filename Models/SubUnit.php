<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUnit extends Model
{
    use HasFactory;

    protected $fillable = ['sub_unit','parent_units_id','medical_test_id','convert_unit'];
    public $timestamps = false;
    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnits::class, 'measurement_units_id');
    }
    public function parentUnit()
    {
        return $this->belongsTo(ParentUnit::class,'parent_units_id');
    }
    public function medicalTest(){
        return $this->belongsTo(MedicalTest::class);
    }

}
