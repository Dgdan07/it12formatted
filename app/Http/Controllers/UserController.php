<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        
        $query = User::with(['role', 'disabledBy']);

        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('f_name', 'like', '%' . $search . '%')
                  ->orWhere('m_name', 'like', '%' . $search . '%')
                  ->orWhere('l_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('contactNo', 'like', '%' . $search . '%')
                  ->orWhereHas('role', function($roleQuery) use ($search) {
                      $roleQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $users = $query->orderBy('id', 'asc')->paginate(10);        
        $roles = Role::all();

        return view('users.index', compact('users', 'roles', 'showArchived'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:50|unique:users',
                'f_name' => 'required|string|max:100',
                'm_name' => 'nullable|string|max:100',
                'l_name' => 'required|string|max:100',
                'contactNo' => 'nullable|string|max:50',
                'role_id' => 'required|exists:roles,id',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            User::create([
                'username' => $request->username,
                'f_name' => ucwords(strtolower($request->f_name)),
                'm_name' => $request->m_name ? ucwords(strtolower($request->m_name)) : null,
                'l_name' => ucwords(strtolower($request->l_name)),
                'contactNo' => $request->contactNo,
                'role_id' => $request->role_id,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            return redirect()->route('users.index')->with('success', 'User added successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('users.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['role', 'disabledBy']);
        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load(['role']);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:50|unique:users,username,' . $user->id,
                'f_name' => 'required|string|max:100',
                'm_name' => 'nullable|string|max:100',
                'l_name' => 'required|string|max:100',
                'contactNo' => 'nullable|string|max:50',
                'role_id' => 'required|exists:roles,id',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|confirmed|min:8',
            ]);

            $updateData = [
                'username' => $request->username,
                'f_name' => ucwords(strtolower($request->f_name)),
                'm_name' => $request->m_name ? ucwords(strtolower($request->m_name)) : null,
                'l_name' => ucwords(strtolower($request->l_name)),
                'contactNo' => $request->contactNo,
                'role_id' => $request->role_id,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('users.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function archive(User $user)
    {
        try {
            $currentUserId = session('user_id');
            
            if ($currentUserId === $user->id) {
                return redirect()->route('users.index')->with('error', 'You cannot archive your own account.');
            }

            $user->update([
                'is_active' => false,
                'date_disabled' => now(),
                'disabled_by_user_id' => $currentUserId,
            ]);

            return redirect()->route('users.index')->with('success', 'User archived successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('users.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function restore(User $user)
    {
        try {
            $user->update([
                'is_active' => true,
                'date_disabled' => null,
                'disabled_by_user_id' => null,
            ]);

            return redirect()->route('users.index', ['archived' => true])->with('success', 'User restored successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('users.index', ['archived' => true])->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function resetPassword(Request $request, User $user)
    {
        try {
            $request->validate([
                'password' => ['required', 'confirmed', 'min:8'],
            ]);

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}