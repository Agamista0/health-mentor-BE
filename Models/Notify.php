<?php

namespace App\Models;

//use App\Traits\ActivityLogTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notify extends Model
{
    public $table='notifies';
    use HasFactory;

    protected $guarded=[];

    public function notification(){
        return $this->belongsTo(Notification::class);
    }

    public function User(){
        return $this->belongsTo(User::class);
    }

}
