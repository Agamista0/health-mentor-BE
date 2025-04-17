<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UserMedicalTest extends Model
{
    use HasFactory;
    protected $guarded = [], $table = 'user_medical_tests';

    public function MedicalTestValue(){
        return $this->belongsTo(MedicalTestValue::class, 'medical_test_value_id');
    }
}
