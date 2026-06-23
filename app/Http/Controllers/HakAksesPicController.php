<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HakAksesPicController extends Controller
{
    public function index()
    {
        // Hanya ambil user dengan role = 'pic'
        $users = User::where('role', 'pic')->get();
        return view('admin.Role', compact('users'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'pic') {
            return redirect()->back()->with('error', 'Hanya pengguna dengan role PIC yang dapat diatur hak aksesnya.');
        }

        $request->validate([
            'admin_permissions' => 'nullable|array',
        ]);

        $user->update([
            'admin_permissions' => $request->admin_permissions ?? [],
        ]);

        return redirect()->back()->with('success', 'Hak Akses PIC berhasil diperbarui.');
    }
}
