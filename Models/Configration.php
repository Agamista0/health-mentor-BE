<?php

namespace App\Models;

use App\Traits\ActivityLogTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configration extends Model
{
    use HasFactory;
    protected $table = 'configrations';

    protected $fillable=[
        "key",
        "value",
    ];
    protected $casts=[
        "value"=>"array"
    ];
}
