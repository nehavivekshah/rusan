<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class CustomerDepartments extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'customer_departments';

    protected $fillable = [
        'client_id',
        'name',
        'location',
        'poc'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }
}
