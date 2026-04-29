<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class Task extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'cid',
        'uid',
        'project_id',
        'parent_id',
        'due_date',
        'title',
        'des',
        'label',
        'whr',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('id', 'asc');
    }

    /**
     * All users this task is assigned to (multi-assign via pivot).
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees', 'task_id', 'user_id')
                    ->withTimestamps();
    }
}
