<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class ReceivedEmail extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'cid', 'inbox_id', 'message_id', 'from_email', 'from_name', 
        'subject', 'body_html', 'body_text', 'received_at', 'is_read', 'lead_id'
    ];

    public function lead()
    {
        return $this->belongsTo(Leads::class, 'lead_id');
    }
}
