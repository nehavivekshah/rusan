<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    /*protected $fillable = [
        'module',
        'event',
        'subject',
        'body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];*/

    protected $fillable = [
        'module','event','subject','body','is_active','reminder_days'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'reminder_days' => 'array', // auto-casts JSON <-> array
    ];

    /* ==============================
     | Scopes
     |==============================*/

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /* ==============================
     | Helpers
     |==============================*/

    public function parse(array $data = [])
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($data as $key => $value) {
            $subject = str_replace('{{'.$key.'}}', $value, $subject);
            $body = str_replace('{{'.$key.'}}', $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
