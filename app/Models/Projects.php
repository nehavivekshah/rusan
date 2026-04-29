<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Projects extends Model
{
    use HasFactory, BelongsToCompany;

    public function salesperson()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoices::class, 'project_id');
    }
}
