<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\StaffSkill;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\EmailHelper;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $query = User::with(['role', 'department', 'skills']);

        if ($request->has('role') && $request->role !== 'all') {
            $query->whereHas('role', fn($q) => $q->where('name', $request->role));
        }

        if ($request->has('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Send welcome email
        $roleName = Role::find($validated['role_id'])->name ?? 'User';
        EmailHelper::sendWelcome($user->email, $user->name, $roleName);

        ActivityLog::log('created', "Created user: {$user->name} ({$user->email})", $user);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load(['role', 'department'])
        ], 201);
    }

    public function show(Request $request, $id)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $user = User::with(['role', 'department', 'skills'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $old = $user->toArray();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::log('updated', "Updated user: {$user->name}", $user, $old, $user->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh()->load(['role', 'department', 'skills'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account'], 400);
        }

        ActivityLog::log('deleted', "Deleted user: {$user->name} ({$user->email})", $user);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Update staff skills
     */
    public function updateSkills(Request $request, $id)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'skills' => 'required|array',
            'skills.*.skill' => 'required|string|max:100',
            'skills.*.proficiency' => 'required|in:beginner,intermediate,expert',
        ]);

        $user = User::findOrFail($id);

        // Replace all skills
        StaffSkill::where('user_id', $id)->delete();

        foreach ($request->skills as $skill) {
            StaffSkill::create([
                'user_id' => $id,
                'skill' => $skill['skill'],
                'proficiency' => $skill['proficiency'],
            ]);
        }

        ActivityLog::log('updated', "Updated skills for staff: {$user->name}", $user);

        return response()->json([
            'success' => true,
            'message' => 'Skills updated successfully',
            'data' => $user->fresh()->load('skills')
        ]);
    }

    /**
     * Get roles list
     */
    public function roles()
    {
        return response()->json([
            'success' => true,
            'data' => Role::all()
        ]);
    }
}
