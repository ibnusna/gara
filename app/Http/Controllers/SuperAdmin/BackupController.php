<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    protected $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups/');
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0775, true);
        }
    }

    public function index()
    {
        $backups = [];
        $files = File::glob($this->backupDir . '*.sql');

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'time' => filemtime($file),
            ];
        }

        usort($backups, fn($a, $b) => $b['time'] - $a['time']);

        return view('super_admin.backup.index', compact('backups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'db_name' => 'required|in:auth_gara,lms_pembelajaran,asesmen_gara,all',
        ]);

        $db_name = $request->db_name;
        $timestamp = date('Y-m-d_H-i-s');

        
        $dbMap = [
            'auth_gara' => 'mysql_auth',
            'lms_pembelajaran' => 'mysql_apps',
            'asesmen_gara' => 'asesmen_gara',
        ];

        $success = false;

        if ($db_name === 'all') {
            $filename = "all_databases_backup_{$timestamp}.sql";
            $filepath = $this->backupDir . $filename;
            
            @unlink($filepath); 
            $overallSuccess = true;
            $first = true;
            foreach ($dbMap as $key => $connName) {
                $mode = $first ? 'w+' : 'a+';
                $res = $this->backupDatabasePhp($connName, $filepath, $mode);
                if (!$res) $overallSuccess = false;
                $first = false;
            }
            $success = $overallSuccess;
        } else {
            $filename = "{$db_name}_backup_{$timestamp}.sql";
            $filepath = $this->backupDir . $filename;
            $connName = $dbMap[$db_name] ?? 'mysql';
            $success = $this->backupDatabasePhp($connName, $filepath, 'w+');
        }

        if ($success && file_exists($filepath) && filesize($filepath) > 0) {
            try {
                DB::connection('mysql_auth')->table('audit_logs')->insert([
                    'user_id' => Auth::id() ?? 1,
                    'user_type' => 'super_admin',
                    'action' => "Backup database: {$db_name}",
                    'module' => 'Database Backup',
                    'ip_address' => request()->ip(),
                ]);
            } catch (\Exception $ignored) {}

            return redirect()->back()->with('success_message', "Backup '{$db_name}' berhasil disimpan sebagai '{$filename}'. Data telah diunduh sepenuhnya beserta isinya (Schema + Data).");
        }

        if (file_exists($filepath)) {
            @unlink($filepath);
        }
        return redirect()->back()->with('error_message', "Gagal membuat backup. Coba periksa koneksi atau memori server.");
    }

    private function backupDatabasePhp($connectionName, $filepath, $mode = 'w+')
    {
        try {
            $pdo = DB::connection($connectionName)->getPdo();
            $dbName = DB::connection($connectionName)->getDatabaseName();
        } catch (\Exception $e) {
            return false;
        }

        $tables = [];
        $query = $pdo->query('SHOW TABLES');
        while ($row = $query->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $handle = fopen($filepath, $mode);
        if (!$handle) return false;

        fwrite($handle, "-- --------------------------------------------------------\n");
        fwrite($handle, "-- Database Backup: {$dbName}\n");
        fwrite($handle, "-- Connection Name: {$connectionName}\n");
        fwrite($handle, "-- Generation Time: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- --------------------------------------------------------\n\n");

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        foreach ($tables as $table) {
            $query = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $query->fetch(\PDO::FETCH_NUM);
            
            fwrite($handle, "--\n-- Table structure for table `{$table}`\n--\n\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $row[1] . ";\n\n");

            $query = $pdo->query("SELECT * FROM `{$table}`");
            $rowCount = $query->rowCount();

            if ($rowCount > 0) {
                fwrite($handle, "--\n-- Dumping data for table `{$table}`\n--\n\n");
                
                while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
                    $keys = array_keys($row);
                    $values = array_values($row);
                    
                    $escapedValues = array_map(function($val) use ($pdo) {
                        if ($val === null) return 'NULL';
                        return $pdo->quote($val);
                    }, $values);
                    
                    $keysString = implode("`, `", $keys);
                    $valuesString = implode(", ", $escapedValues);
                    
                    fwrite($handle, "INSERT INTO `{$table}` (`{$keysString}`) VALUES ({$valuesString});\n");
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n\n");
        fclose($handle);
        return true;
    }

    public function download(Request $request)
    {
        $filename = basename($request->file);
        $filepath = $this->backupDir . $filename;

        if (!file_exists($filepath) || !str_ends_with($filename, '.sql')) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return response()->download($filepath);
    }

    public function destroy(Request $request)
    {
        $filename = basename($request->filename);
        $filepath = $this->backupDir . $filename;

        if (file_exists($filepath) && str_ends_with($filename, '.sql')) {
            @unlink($filepath);
            return redirect()->back()->with('success_message', "File backup '{$filename}' berhasil dihapus.");
        }

        return redirect()->back()->with('error_message', 'Gagal menghapus file backup.');
    }
}
