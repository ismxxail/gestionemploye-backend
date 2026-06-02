<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWorkDay extends Model
{
    protected $fillable = [
        'user_id',
        'work_day_id',
    ];
}
