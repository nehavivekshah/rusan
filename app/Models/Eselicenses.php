<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Eselicenses extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'cid',
        'project_id',
        'client_name',
        'company',
        'mob',
        'email',
        'project_name',
        'type',
        'amount',
        'deployment_url',
        'technology_stack',
        'note',
        'eselicense_key',
        'expiry_date',
        'status',
    ];
}
