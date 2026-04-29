<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Leads extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'cid',
        'uid',
        'name',
        'company',
        'email',
        'mob',
        'gstno',
        'location',
        'purpose',
        'assigned',
        'poc',
        'status',
        'whatsapp',
        'position',
        'industry',
        'website',
        'values',
        'language',
        'tags',
        'gst_no',
        'score',
        'is_duplicate',
        'source'
    ];
}
