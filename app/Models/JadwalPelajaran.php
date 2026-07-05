<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'jadwal_pelajaran';

    protected $fillable = [
        'jam_pelajaran_id',
        'teaching_assignment_id'
    ];

    public $timestamps = false;

    public function masterJam()
    {
        return $this->belongsTo(MasterJamPelajaran::class, 'jam_pelajaran_id');
    }

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class, 'teaching_assignment_id');
    }
}
