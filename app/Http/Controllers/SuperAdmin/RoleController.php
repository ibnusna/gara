<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $all_permissions = Permission::orderBy('id')->get();

        return view('super_admin.roles.index', compact('roles', 'all_permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|unique:mysql_auth.roles,role_name'
        ]);

        try {
            DB::connection('mysql_auth')->beginTransaction();

            $role = Role::create([
                'role_name' => $request->role_name
            ]);

            if ($request->has('permissions') && is_array($request->permissions)) {
                $role->permissions()->sync($request->permissions);
            }

            DB::connection('mysql_auth')->commit();
            return redirect()->back()->with('success_message', "Role '{$role->role_name}' berhasil ditambahkan.");
        } catch (\Exception $e) {
            DB::connection('mysql_auth')->rollBack();
            return redirect()->back()->with('error_message', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'role_name' => 'required|string|unique:mysql_auth.roles,role_name,' . $id
        ]);

        try {
            DB::connection('mysql_auth')->beginTransaction();

            $role = Role::findOrFail($id);
            $role->update(['role_name' => $request->role_name]);

            if ($request->has('permissions') && is_array($request->permissions)) {
                $role->permissions()->sync($request->permissions);
            } else {
                $role->permissions()->detach();
            }

            DB::connection('mysql_auth')->commit();
            return redirect()->back()->with('success_message', "Role '{$role->role_name}' berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::connection('mysql_auth')->rollBack();
            return redirect()->back()->with('error_message', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if ($id == 1) {
            return redirect()->back()->with('error_message', 'Super Admin tidak dapat dihapus.');
        }

        try {
            Role::findOrFail($id)->delete();
            return redirect()->back()->with('success_message', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Gagal menghapus role.');
        }
    }
}
