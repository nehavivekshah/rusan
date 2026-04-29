<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class CustomerDepartment extends Model
{
    use HasFactory, BelongsToCompany;
}
