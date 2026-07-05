<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    use HasFactory;

    protected $connection = 'mysql_apps';
    protected $table = 'class_subjects';

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'subject_id'
    ];
}
