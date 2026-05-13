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
        'whatsapp',
        'position',
        'industry',
        'interested_product',
        'first_call',
        'sms_opt',
        'sms_opt_out',
        'email_opt_out',
        'address',
        'city',
        'state',
        'country',
        'pin_code',
        'website',
        'values',
        'language',
        'tags',
        'gst_no',
        'score',
        'is_duplicate',
        'source'
    ];
}
