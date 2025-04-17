<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyStatus extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function Details(){
        return $this->hasMAny(BodyStatusDetail::class, 'body_status_id');
    }

}
