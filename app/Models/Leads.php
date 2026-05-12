<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Leads extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'cid',
        'uid',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'dob',
        'progress',
        'company',
        'email',
        'mob',
        'gstno',
        'location',
        'purpose',
        'assigned',
        'poc',
        'status',
        'whatsapp',
        'position',
        'industry',
        'interested_product',
        'first_call',
        'sms_opt',
        'website',
        'values',
        'language',
        'tags',
        'gst_no',
        'score',
        'is_duplicate',
        'source',
        'lead_state',
        'last_call_feedback',
        'last_call_comment',
        'next_call_date',
        'marketing_source',
        'age',
        'consumption_years',
        'tobacco_frequency',
        'craving_for_smoking',
        'problem_smoking',
        'experience_intense_craving',
        'attachment',
    ];

    protected $casts = [
        'dob' => 'date',
        'next_call_date' => 'datetime',
        'first_call' => 'boolean',
        'sms_opt' => 'boolean',
        'age' => 'integer',
        'consumption_years' => 'integer',
        'tobacco_frequency' => 'integer',
    ];

    /**
     * Get the full display name (computed from first/middle/last or fallback to name).
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]);

        return !empty($parts) ? implode(' ', $parts) : ($this->name ?? '');
    }
}
