<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Invoice_items extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'invoice_items';

    protected $guarded = ['id'];

    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_id');
    }
}
