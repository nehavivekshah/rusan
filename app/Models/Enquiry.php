<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

    protected $fillable = [
        'name',
        'email',
        'mob',
        'subject',
        'message',
        'status'
    ];
}
