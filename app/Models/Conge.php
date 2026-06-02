<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Conge extends Model

{
    use HasFactory; 

    protected $fillable = [
        'employeur_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'admin_comment',
    ];
}
