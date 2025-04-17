<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminationDetails extends Model
{
    use HasFactory;

    protected $table = 'examination_details';
    protected $fillable = ['title','about','examination_id'];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }
}
