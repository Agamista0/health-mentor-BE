<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advice extends Model
{
    use HasFactory;
    protected $table ='advice', $guarded = [];

    public function MedicalTestValue(){
        return $this->belongsTo(MedicalTestValue::class, 'medical_test_id');
    }
}
