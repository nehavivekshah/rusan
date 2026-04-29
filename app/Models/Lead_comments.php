<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Lead_comments extends Model
{
    use HasFactory, BelongsToCompany;

    protected $guarded = [];
}
