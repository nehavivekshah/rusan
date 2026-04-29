<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Proposals extends Model
{
    use HasFactory, BelongsToCompany;
    protected $fillable = [
        'cid', 'lead_id', 'client_name', 'client_email', 'client_phone', 'client_address', 
        'client_city', 'client_state', 'client_zip', 'client_country',
        'subject', 'related', 'proposal_date', 'open_till', 'currency', 
        'discount_type', 'notes', 'sub_total', 'discount_percentage', 
        'discount_amount_calculated', 'cgst_total', 'sgst_total', 'igst_total', 'vat_total', 
        'adjustment_amount', 'grand_total', 'status', 'tags', 'secure_token'
    ];
}
