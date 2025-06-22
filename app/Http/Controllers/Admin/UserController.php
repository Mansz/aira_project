<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
{
    $users = User::paginate(10); // Menggunakan paginasi
    return view('admin.users.index', compact('users'));
}

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'wa_number' => 'required|string|max:20|unique:users,wa_number,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function block(User $user)
    {
        $user->blocked = true;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User blocked successfully.');
    }

    public function unblock(User $user)
    {
        $user->blocked = false;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User unblocked successfully.');
    }
}
