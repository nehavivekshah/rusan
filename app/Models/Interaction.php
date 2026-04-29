<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

    protected $fillable = [
        'user_id',
        'rel_type',
        'rel_id',
        'type',
        'content',
        'attachment_path'
    ];
}
