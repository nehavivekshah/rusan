<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Clients extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'name',
        'email',
        'mob',
        'company',
        'lifecycle_stage',
        'industry',
        'website',
        'location',
        'status',
        'poc',
        'whatsapp',
        'position',
        'values',
        'language',
        'purpose',
        'tags',
        'commentLeadID',
        'source',
        'alterMob',
        'gstno'
    ];

    public function departments()
    {
        return $this->hasMany(CustomerDepartments::class, 'client_id');
    }
}
