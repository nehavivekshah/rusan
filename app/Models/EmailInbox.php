<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class EmailInbox extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'cid', 'user_id', 'email', 'imap_host', 'imap_port', 
        'imap_encryption', 'username', 'password', 'status', 'last_sync_at'
    ];
}
