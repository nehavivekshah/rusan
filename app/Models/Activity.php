<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Activity extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'user_id',
        'cid',
        'type',
        'module',
        'subject_id',
        'subject_label',
        'description',
        'value',
        'ip_address',
        'user_agent',
    ];

    /* ---------- Relationships ---------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ---------- Scopes ---------- */

    /** Filter by company */
    public function scopeForCompany($query, $cid)
    {
        return $query->where('cid', $cid);
    }

    /** Only activities of a specific module */
    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }
}
