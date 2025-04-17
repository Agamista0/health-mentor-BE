<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    public function media(){
        return $this->morphMany(Media::class, 'model');
    }

    public function Details(){
        return $this->hasOne(FileDetail::class, 'file_id');
    }
}
