<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

    protected $fillable = [
        'customer_id',
        'user_id',
        'name',
        'stage',
        'amount',
        'expected_close_date',
        'win_loss_reason'
    ];
}
