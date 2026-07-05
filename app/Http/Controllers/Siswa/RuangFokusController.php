<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RuangFokusController extends Controller
{
    public function index()
    {
        return view('siswa.ruang_fokus.index');
    }
}

