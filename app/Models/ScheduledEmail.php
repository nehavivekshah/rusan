<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledEmail extends Model
{
    use HasFactory;

    protected $table = 'scheduled_emails';

    protected $fillable = [
        'tracking_token',
        'cid',
        'user_id',
        'recipient',
        'subject',
        'body',
        'scheduled_at',
        'sent_at',
        'status',
        'opened_at',
        'clicked_at',
    ];
}
