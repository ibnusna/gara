<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    use HasFactory;

    protected $connection = 'mysql_apps';
    protected $table = 'teacher_subjects';

    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'subject_id'
    ];
}
