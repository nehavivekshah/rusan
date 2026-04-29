<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class SmtpSettings extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'cid', 'user_id', 'mailer', 'host', 'port', 'username',
        'password', 'encryption', 'from_address', 'from_name'
    ];
}
