<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkDay extends Model
{
    protected $fillable = [
        'day_name',
        'start_time',
        'end_time',
    ];
}
