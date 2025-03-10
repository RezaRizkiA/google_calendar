<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function showRoleForm()
    {
        // Pastikan user sudah login
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('google.login')
                ->with('error', 'Harus login dulu dengan Google!');
        }

        // Tampilkan form untuk pilih role
        return view('choose-role.role', compact('user'));
    }

    public function processRoleForm(Request $request)
    {
        $request->validate([
            'role' => 'required|in:student,teacher',
        ]);

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('google.login')
                ->with('error', 'Harus login dulu dengan Google!');
        }

        // Update user
        $user->role = $request->input('role');
        $user->save();

        return redirect()->route('student-teacher.showCreateForm')
            ->with('success', 'Role berhasil disimpan!');
    }
}
