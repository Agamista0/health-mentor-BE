<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalTestValue extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function MedicalTest(){
        return $this->belongsTo(MedicalTest::class);
    }
}
