<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'mob',
        'gst',
        'vat',
        'tax',
        'bank_details',
        'address',
        'city',
        'state',
        'zipcode',
        'country',
        'subscription',
        'logo',
        'img',
        'pdf_logo',
        'lifecycle_stage',
        'industry',
        'website'
    ];
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'company_id');
    }
}
