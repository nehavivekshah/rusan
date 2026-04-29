<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo_lists extends Model
{
    use HasFactory;
    protected $table = 'todo_lists';
    protected $guarded = [];
}
