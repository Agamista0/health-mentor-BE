<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleDoctor extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    public function speciality(){
        return $this->belongsTo(Speciality::class);
    }
}
