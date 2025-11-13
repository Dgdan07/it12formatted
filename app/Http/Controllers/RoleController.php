<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $roles = $query->latest()->paginate(10);
        return view('roles.index', compact('roles'));
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
                'name' => 'required|string|max:255|unique:roles,name',
                'description' => 'nullable|string',
            ]);

            $capitalizedName = ucwords(strtolower($request->name));

            Role::create([
                'name' => $capitalizedName,
                'description' => $request->description,
            ]);

            return redirect()->route('roles.index')->with('success', 'Role added successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('roles.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return response()->json($role);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        try {
            if ($role->is_protected) {
                return redirect()->route('roles.index')->with('error', 'Core roles cannot be updated.');
            }
            
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
                'description' => 'nullable|string',
            ]);

            $capitalizedName = ucwords(strtolower($request->name));

            $role->update([
                'name' => $capitalizedName,
                'description' => $request->description,
            ]);

            return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('roles.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        try {
            if ($role->is_protected) {
                return redirect()->route('roles.index')->with('error', 'The Administrator role cannot be deleted');
            }

            if ($role->users()->exists()) {
                return redirect()->route('roles.index')->with('error', 'Cannot delete role. There are users associated with this role.');
            }

            $role->delete();

            return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('roles.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}