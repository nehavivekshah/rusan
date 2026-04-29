<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Product extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'cid',
        'name',
        'sku',
        'description',
        'price',
        'category',
        'status',
    ];
}
