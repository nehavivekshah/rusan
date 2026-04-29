<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Invoices extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'invoices';

    protected $guarded = ['id'];
    
    public function client() {
        return $this->belongsTo(Clients::class);
    }

    public function project() {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function items() {
        return $this->hasMany(Invoice_items::class, 'invoice_id');
    }
}
