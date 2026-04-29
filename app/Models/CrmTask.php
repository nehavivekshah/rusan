<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class CrmTask extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'user_id',
        'rel_type',
        'rel_id',
        'project_id',
        'parent_id',
        'name',
        'type',
        'due_date',
        'status'
    ];

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function parent()
    {
        return $this->belongsTo(CrmTask::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(CrmTask::class, 'parent_id')->orderBy('due_date', 'asc');
    }
}
