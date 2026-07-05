<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RuangCatatanController extends Controller
{
    public function index()
    {
        return view('siswa.ruang_catatan.index');
    }
}
