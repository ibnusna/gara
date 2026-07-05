<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\IpBlock;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_pengguna' => 0,
            'total_peran' => 0,
            'total_audit_log' => 0,
            'total_ip_terblokir' => 0
        ];

        try {
            $stats['total_pengguna'] = User::count();
            $stats['total_peran'] = Role::count();

            try {
                $stats['total_audit_log'] = AuditLog::count();
            } catch (\Exception $e) {
                $stats['total_audit_log'] = 'N/A';
            }

            try {
                $stats['total_ip_terblokir'] = IpBlock::count();
            } catch (\Exception $e) {
                $stats['total_ip_terblokir'] = 'N/A';
            }

        } catch (\Exception $e) {
            
        }

        return view('super_admin.dashboard', compact('stats'));
    }
}
