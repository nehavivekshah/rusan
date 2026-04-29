<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'item_id',
        'template_id',
        'days_before',
        'recipient_email',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
