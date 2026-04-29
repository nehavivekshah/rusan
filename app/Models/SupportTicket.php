<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

    protected $fillable = [
        'ticket_no',
        'cid',
        'subject',
        'description',
        'priority',
        'status'
    ];

    /**
     * Get the company that owns the ticket.
     */
    public function company()
    {
        return $this->belongsTo(Companies::class, 'cid');
    }
}
