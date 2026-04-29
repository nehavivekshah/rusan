<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class ThirdPartySetting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'cid',
        'provider',
        'api_key',
        'api_token',
        'account_sid',
        'from_number',
        'additional_config',
        'status',
    ];

    protected $casts = [
        'additional_config' => 'array',
    ];
}
