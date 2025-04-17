<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $table = 'user_groups';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable =
        [
            'name',
            'appKey',
            'order_num',
            'purchases_amount',
            'created_before',
        ];
}
