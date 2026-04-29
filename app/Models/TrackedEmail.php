<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class TrackedEmail extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'cid',
        'recipient',
        'subject',
        'tracking_token',
        'opens',
        'clicks',
        'last_open',
        'last_click'
    ];
}
