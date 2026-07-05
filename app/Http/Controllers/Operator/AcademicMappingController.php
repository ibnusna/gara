<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Guru;
use App\Models\ClassSubject;
use App\Models\TeacherSubject;
use App\Models\TeachingAssignment;
use App\Models\AppSetting;

class AcademicMappingController extends Controller
{
    
    public function curriculumIndex(Request $request)
    {
        $kelases = Kelas::getSortedClasses();
        $mapels = MataPelajaran::orderBy('nama_mapel', 'asc')->get();

        $selected_class_id = $request->get('class_id');
        $class_curriculum = [];

        if ($selected_class_id) {
            $class_curriculum = ClassSubject::where('class_id', $selected_class_id)->pluck('subject_id')->toArray();
        }

        return view('operator.mapping.curriculum', compact('kelases', 'mapels', 'selected_class_id', 'class_curriculum'));
    }

    public function updateCurriculum(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:mysql_auth.kelas,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:mysql_auth.mata_pelajaran,id'
        ]);

        try {
            DB::connection('mysql_apps')->beginTransaction();

            
            ClassSubject::where('class_id', $request->class_id)->delete();

            
            if ($request->has('subjects') && is_array($request->subjects)) {
                $inserts = [];
                foreach ($request->subjects as $subject_id) {
                    $inserts[] = [
                        'class_id' => $request->class_id,
                        'subject_id' => $subject_id
                    ];
                }
                ClassSubject::insert($inserts);
            }

            
            if ($request->has('subjects')) {
                TeachingAssignment::where('class_id', $request->class_id)
                    ->whereNotIn('subject_id', $request->subjects)
                    ->delete();
            } else {
                TeachingAssignment::where('class_id', $request->class_id)->delete();
            }

            DB::connection('mysql_apps')->commit();

            return redirect()->route('operator.mapping.curriculum', ['class_id' => $request->class_id])
                ->with('success_message', 'Kurikulum kelas berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::connection('mysql_apps')->rollBack();
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    
    public function competencyIndex(Request $request)
    {
        $gurus = Guru::orderBy('nama_lengkap', 'asc')->get();
        $mapels = MataPelajaran::orderBy('nama_mapel', 'asc')->get();

        $selected_guru_id = $request->get('guru_id');
        $guru_competencies = [];
        $teacherAssignments = [];

        
        $subjectClassesRaw = DB::connection('mysql_apps')->table('class_subjects')
            ->join(config('database.connections.mysql_auth.database').'.kelas', 'class_subjects.class_id', '=', 'kelas.id')
            ->select('class_subjects.subject_id', 'kelas.id as class_id', 'kelas.nama_kelas', 'kelas.tingkat')
            ->get();
            
        // Sort using the same logic as Kelas::getSortedClasses()
        $subjectClassesRaw = $subjectClassesRaw->sort(function ($a, $b) {
            $tingkatA = \App\Models\Kelas::romanToInt($a->tingkat);
            if ($tingkatA === 0) $tingkatA = (int)$a->tingkat;
            $tingkatB = \App\Models\Kelas::romanToInt($b->tingkat);
            if ($tingkatB === 0) $tingkatB = (int)$b->tingkat;
            if ($tingkatA === $tingkatB) {
                return strnatcasecmp($a->nama_kelas, $b->nama_kelas);
            }
            return $tingkatA <=> $tingkatB;
        })->values();
            
        $subjectClasses = [];
        foreach ($subjectClassesRaw as $row) {
            $subjectClasses[$row->subject_id][] = ['id' => $row->class_id, 'nama_kelas' => $row->nama_kelas];
        }

        $otherAssignments = [];

        if ($selected_guru_id) {
            $guru_competencies = TeacherSubject::where('teacher_id', $selected_guru_id)->pluck('subject_id')->toArray();
            
            $allAssignments = TeachingAssignment::all();
            foreach ($allAssignments as $assignment) {
                if ($assignment->teacher_id == $selected_guru_id) {
                    $teacherAssignments[$assignment->subject_id][] = $assignment->class_id;
                } else {
                    $otherAssignments[$assignment->subject_id][] = $assignment->class_id;
                }
            }
        }

        $allClasses = Kelas::getSortedClasses();

        return view('operator.mapping.competency', compact('gurus', 'mapels', 'selected_guru_id', 'guru_competencies', 'subjectClasses', 'teacherAssignments', 'otherAssignments', 'allClasses'));
    }

    public function updateCompetency(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:mysql_auth.guru,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:mysql_auth.mata_pelajaran,id',
            'classes' => 'nullable|array',
        ]);

        try {
            DB::connection('mysql_apps')->beginTransaction();

            $academicYear = AppSetting::where('setting_key', 'academic_year')->value('setting_value') ?? date('Y') . '/' . (date('Y') + 1);

            
            TeacherSubject::where('teacher_id', $request->teacher_id)->delete();
            TeachingAssignment::where('teacher_id', $request->teacher_id)->delete();

            
            if ($request->has('subjects') && is_array($request->subjects)) {
                $compInserts = [];
                $assignInserts = [];
                
                foreach ($request->subjects as $subject_id) {
                    $compInserts[] = [
                        'teacher_id' => $request->teacher_id,
                        'subject_id' => $subject_id
                    ];
                    
                    if (isset($request->classes[$subject_id]) && is_array($request->classes[$subject_id])) {
                        foreach ($request->classes[$subject_id] as $class_id) {
                            $assignInserts[] = [
                                'class_id' => $class_id,
                                'subject_id' => $subject_id,
                                'teacher_id' => $request->teacher_id,
                                'academic_year' => $academicYear
                            ];
                        }
                    }
                }
                
                TeacherSubject::insert($compInserts);
                if (count($assignInserts) > 0) {
                    TeachingAssignment::insert($assignInserts);
                }
            }

            DB::connection('mysql_apps')->commit();

            return redirect()->route('operator.mapping.competency', ['guru_id' => $request->teacher_id])
                ->with('success_message', 'Kompetensi dan penugasan guru berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::connection('mysql_apps')->rollBack();
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function checkClassAvailability(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'teacher_id' => 'required'
        ]);

        $exists = DB::connection('mysql_apps')->table('teaching_assignments')
            ->join(config('database.connections.mysql_auth.database').'.guru', 'teaching_assignments.teacher_id', '=', 'guru.id')
            ->where('teaching_assignments.class_id', $request->class_id)
            ->where('teaching_assignments.subject_id', $request->subject_id)
            ->where('teaching_assignments.teacher_id', '!=', $request->teacher_id)
            ->select('guru.nama_lengkap')
            ->first();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => 'Kelas ini sudah diampu oleh ' . $exists->nama_lengkap . ' untuk mapel ini.'
            ]);
        }

        return response()->json(['available' => true]);
    }

    
    public function assignmentsIndex()
    {
        $assignmentsRaw = DB::connection('mysql_apps')->table('teaching_assignments as ta')
            ->join(config('database.connections.mysql_auth.database').'.guru as g', 'ta.teacher_id', '=', 'g.id')
            ->join(config('database.connections.mysql_auth.database').'.mata_pelajaran as m', 'ta.subject_id', '=', 'm.id')
            ->join(config('database.connections.mysql_auth.database').'.kelas as k', 'ta.class_id', '=', 'k.id')
            ->select('ta.id', 'ta.teacher_id', 'g.nama_lengkap', 'm.nama_mapel', 'k.nama_kelas', 'k.tingkat')
            ->get();

        $assignmentsRaw = $assignmentsRaw->sort(function ($a, $b) {
            $cmp = strcmp($a->nama_lengkap, $b->nama_lengkap);
            if ($cmp !== 0) return $cmp;
            $cmp2 = strcmp($a->nama_mapel, $b->nama_mapel);
            if ($cmp2 !== 0) return $cmp2;
            
            $tingkatA = \App\Models\Kelas::romanToInt($a->tingkat);
            if ($tingkatA === 0) $tingkatA = (int)$a->tingkat;
            $tingkatB = \App\Models\Kelas::romanToInt($b->tingkat);
            if ($tingkatB === 0) $tingkatB = (int)$b->tingkat;
            
            if ($tingkatA === $tingkatB) {
                return strnatcasecmp($a->nama_kelas, $b->nama_kelas);
            }
            return $tingkatA <=> $tingkatB;
        })->values();

        $assignments = [];
        foreach ($assignmentsRaw as $row) {
            $assignments[$row->nama_lengkap][$row->nama_mapel][] = [
                'id' => $row->id,
                'nama_kelas' => $row->nama_kelas
            ];
        }

        $kelases = Kelas::getSortedClasses();

        
        $currRaw = DB::connection('mysql_apps')->table('class_subjects as cs')
            ->join(config('database.connections.mysql_auth.database').'.mata_pelajaran as m', 'cs.subject_id', '=', 'm.id')
            ->select('cs.class_id', 'cs.subject_id', 'm.nama_mapel')
            ->get();

        $curriculumMap = [];
        foreach ($currRaw as $r) {
            $curriculumMap[$r->class_id][] = ['id' => $r->subject_id, 'name' => $r->nama_mapel];
        }

        
        $compRaw = DB::connection('mysql_apps')->table('teacher_subjects as ts')
            ->join(config('database.connections.mysql_auth.database').'.guru as g', 'ts.teacher_id', '=', 'g.id')
            ->select('ts.subject_id', 'ts.teacher_id', 'g.nama_lengkap')
            ->get();

        $competencyMap = [];
        foreach ($compRaw as $r) {
            $competencyMap[$r->subject_id][] = ['id' => $r->teacher_id, 'name' => $r->nama_lengkap];
        }

        return view('operator.mapping.assignments', compact('assignments', 'kelases', 'curriculumMap', 'competencyMap'));
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:mysql_auth.kelas,id',
            'subject_id' => 'required|exists:mysql_auth.mata_pelajaran,id',
            'teacher_id' => 'required|exists:mysql_auth.guru,id',
        ]);

        try {
            
            $maxBeban = (int) (AppSetting::where('setting_key', 'max_beban_mengajar')->value('setting_value') ?? 24);
            $totalAssignments = TeachingAssignment::where('teacher_id', $request->teacher_id)->count();

            if ($totalAssignments >= $maxBeban) {
                return redirect()->back()->with(
                    'error_message',
                    "Guru ini sudah mencapai batas maksimal beban mengajar ({$totalAssignments}/{$maxBeban} kelas). "
                    . "Harap kurangi penugasan lain sebelum menambahkan yang baru."
                );
            }

            
            $exists = TeachingAssignment::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error_message', 'Mata pelajaran tersebut sudah memiliki guru pengampu di kelas ini.');
            }

            $academicYear = AppSetting::where('setting_key', 'academic_year')->value('setting_value') ?? date('Y') . '/' . (date('Y') + 1);

            TeachingAssignment::create([
                'class_id'     => $request->class_id,
                'subject_id'   => $request->subject_id,
                'teacher_id'   => $request->teacher_id,
                'academic_year' => $academicYear,
            ]);

            return redirect()->back()->with('success_message', 'Penugasan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroyAssignment($id)
    {
        try {
            $assignment = TeachingAssignment::findOrFail($id);
            $assignment->delete();

            return redirect()->back()->with('success_message', 'Penugasan mengajar berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function jadwalIndex(Request $request)
    {
        $kelases = Kelas::getSortedClasses();
        $selected_class_id = $request->get('class_id');

        $masterJam = \App\Models\MasterJamPelajaran::orderBy('jam_mulai', 'asc')->get();
        $groupedMaster = $masterJam->groupBy('hari');
        
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $jadwalData = [];
        $classCurriculum = [];
        if ($selected_class_id) {
            $classCurriculumRaw = DB::connection('mysql_apps')->table('teaching_assignments as ta')
                ->join(config('database.connections.mysql_auth.database').'.mata_pelajaran as m', 'ta.subject_id', '=', 'm.id')
                ->join(config('database.connections.mysql_auth.database').'.guru as g', 'ta.teacher_id', '=', 'g.id')
                ->where('ta.class_id', $selected_class_id)
                ->select('ta.id', 'm.id as mapel_id', 'm.nama_mapel', 'g.id as guru_id', 'g.nama_lengkap')
                ->get();
            $classCurriculum = $classCurriculumRaw;

            $jadwalRaw = \DB::connection('mysql_auth')->table('jadwal_pelajaran as jp')
                ->join(config('database.connections.mysql_apps.database').'.teaching_assignments as ta', 'jp.teaching_assignment_id', '=', 'ta.id')
                ->where('ta.class_id', $selected_class_id)
                ->select('jp.*')
                ->get();

            foreach ($jadwalRaw as $j) {
                $jadwalData[$j->jam_pelajaran_id] = $j;
            }
        }

        return view('operator.mapping.jadwal', compact('kelases', 'selected_class_id', 'hariList', 'groupedMaster', 'jadwalData', 'classCurriculum'));
    }



    public function storeJadwal(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'jam_pelajaran_id' => 'required',
            'teaching_assignment_id' => 'required'
        ]);

        $ta = DB::connection('mysql_apps')->table('teaching_assignments')->where('id', $request->teaching_assignment_id)->first();
        if (!$ta) return response()->json(['success' => false, 'message' => 'Penugasan tidak ditemukan.']);

        $conflict = \DB::connection('mysql_auth')->table('jadwal_pelajaran as jp')
            ->join(config('database.connections.mysql_apps.database').'.teaching_assignments as ta', 'jp.teaching_assignment_id', '=', 'ta.id')
            ->where('jp.jam_pelajaran_id', $request->jam_pelajaran_id)
            ->where('ta.teacher_id', $ta->teacher_id)
            ->where('ta.class_id', '!=', $request->class_id)
            ->first();

        if ($conflict) {
            $conflictClass = Kelas::find($conflict->class_id)->nama_kelas ?? 'Kelas Lain';
            return response()->json([
                'success' => false,
                'message' => 'Bentrok! Guru tersebut sudah terjadwal di ' . $conflictClass . ' pada jam ini.'
            ]);
        }

        $existingJadwalId = \DB::connection('mysql_auth')->table('jadwal_pelajaran as jp')
            ->join(config('database.connections.mysql_apps.database').'.teaching_assignments as ta', 'jp.teaching_assignment_id', '=', 'ta.id')
            ->where('jp.jam_pelajaran_id', $request->jam_pelajaran_id)
            ->where('ta.class_id', $request->class_id)
            ->value('jp.id');

        if ($existingJadwalId) {
            \App\Models\JadwalPelajaran::where('id', $existingJadwalId)->update([
                'teaching_assignment_id' => $request->teaching_assignment_id
            ]);
        } else {
            \App\Models\JadwalPelajaran::create([
                'jam_pelajaran_id' => $request->jam_pelajaran_id,
                'teaching_assignment_id' => $request->teaching_assignment_id
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function destroyJadwal($id, Request $request)
    {
        $existingJadwalId = \DB::connection('mysql_auth')->table('jadwal_pelajaran as jp')
            ->join(config('database.connections.mysql_apps.database').'.teaching_assignments as ta', 'jp.teaching_assignment_id', '=', 'ta.id')
            ->where('jp.jam_pelajaran_id', $id)
            ->where('ta.class_id', $request->class_id)
            ->value('jp.id');

        if ($existingJadwalId) {
            \App\Models\JadwalPelajaran::where('id', $existingJadwalId)->delete();
        }
        return response()->json(['success' => true]);
    }

    public function waktuIndex()
    {
        $masterJam = \App\Models\MasterJamPelajaran::orderBy('hari', 'asc')->orderBy('jam_mulai', 'asc')->get();
        $settingsDB = \App\Models\AppSetting::pluck('setting_value', 'setting_key')->toArray();
        return view('operator.mapping.waktu', compact('masterJam', 'settingsDB'));
    }

    public function storeWaktu(Request $request)
    {
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'jenis_kegiatan' => 'required'
        ]);

        \App\Models\MasterJamPelajaran::create([
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'urutan_jam' => $request->urutan_jam
        ]);

        return redirect()->back()->with('success_message', 'Waktu belajar berhasil ditambahkan.');
    }

    public function updateWaktu(Request $request, $id)
    {
        $slot = \App\Models\MasterJamPelajaran::findOrFail($id);
        
        $oldEnd = strtotime($slot->jam_selesai);
        $newEnd = strtotime($request->jam_selesai);
        $diffMinutes = ($newEnd - $oldEnd) / 60;

        $slot->update([
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'urutan_jam' => $request->urutan_jam
        ]);

        if ($request->has('auto_shift') && $diffMinutes != 0) {
            $subsequentSlots = \App\Models\MasterJamPelajaran::where('hari', $slot->hari)
                ->where('id', '!=', $id)
                ->where('jam_mulai', '>=', date('H:i:s', $oldEnd))
                ->orderBy('jam_mulai', 'asc')
                ->get();

            foreach ($subsequentSlots as $s) {
                $sStart = strtotime($s->jam_mulai);
                $sEnd = strtotime($s->jam_selesai);
                
                $newSStart = date('H:i:s', strtotime(($diffMinutes > 0 ? '+' : '') . $diffMinutes . ' minutes', $sStart));
                $newSEnd = date('H:i:s', strtotime(($diffMinutes > 0 ? '+' : '') . $diffMinutes . ' minutes', $sEnd));
                
                $s->update([
                    'jam_mulai' => $newSStart,
                    'jam_selesai' => $newSEnd
                ]);
            }
        }

        return redirect()->back()->with('success_message', 'Slot waktu berhasil diperbarui.');
    }

    public function destroyWaktu($id)
    {
        \App\Models\MasterJamPelajaran::where('id', $id)->delete();
        return redirect()->back()->with('success_message', 'Waktu belajar berhasil dihapus.');
    }
}
