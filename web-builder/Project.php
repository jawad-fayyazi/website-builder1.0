<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_name',
        'project_json',
    ];

    // Define the table name if it's not the plural of the model name
    protected $table = 'projects_data';
}
