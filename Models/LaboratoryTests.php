<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LaboratoryTests extends Model
{
    use HasFactory;
    protected $guarded = [];


    // public function LaboratoryTests()
    // {
    //     return $this->hasMany(LaboratoryTests::class);
    // }
    public function files()
    {
        return $this->hasMany(LaboratoryFiles::class, 'laboratory_test_id');
    }

    public function User_data(){
        return $this->belongsTo(User::class , 'user_id');
    }

    public function user_medical_data()
    {
        return $this->belongsTo(UserMedicalTest::class,'id','laboratory_id');
        
    }

    public function user_medical_data_All()
    {
        return $this->hasMany(UserMedicalTest::class,'laboratory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
