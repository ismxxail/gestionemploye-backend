<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presence extends Model
{
    use HasFactory;
    protected $fillable = [
        'employeur_id',
        'date',
        'check_in',
        'check_out',
    ];
}
