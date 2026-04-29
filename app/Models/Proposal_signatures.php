<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Proposal_signatures extends Model
{
    use HasFactory, BelongsToCompany;
    protected $table = 'proposal_signatures';

    protected $fillable = [
        'proposal_id',
        'token',
        'first_name',
        'last_name',
        'email',
        'signature_path',
    ];
}
