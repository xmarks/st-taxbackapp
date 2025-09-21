<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index(Request $request): View
    {
        Gate::authorize('manage-users');

        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        Gate::authorize('manage-users');

        $roles = \App\Providers\AuthServiceProvider::getAssignableRoles(auth()->user());
        return view('admin.users.create', compact('roles'));
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        if (!$this->canManageUser(auth()->user(), $user)) {
            abort(403, 'You cannot edit this user.');
        }

        $roles = $this->getAvailableRoles(auth()->user());
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if (!$this->canManageUser(auth()->user(), $user)) {
            abort(403, 'You cannot edit this user.');
        }

        $availableRoles = $this->getAvailableRoles(auth()->user());

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($availableRoles)],
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (!$this->canManageUser(auth()->user(), $user)) {
            abort(403, 'You cannot delete this user.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    private function canManageUsers(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return in_array($user->role, ['Manager', 'Admin', 'SuperAdmin']);
    }

    private function canManageUser(?User $currentUser, User $targetUser): bool
    {
        if (!$currentUser) {
            return false;
        }

        // Users cannot manage users of higher or equal role (except SuperAdmin can manage everyone)
        if ($currentUser->role === 'SuperAdmin') {
            return true;
        }

        if ($currentUser->role === 'Admin') {
            return !in_array($targetUser->role, ['Admin', 'SuperAdmin']);
        }

        if ($currentUser->role === 'Manager') {
            return $targetUser->role === 'User';
        }

        return false;
    }

    private function getAvailableRoles(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $allRoles = ['User', 'Manager', 'Admin', 'SuperAdmin'];

        if ($user->role === 'SuperAdmin') {
            return $allRoles;
        }

        if ($user->role === 'Admin') {
            return ['User', 'Manager', 'Admin'];
        }

        if ($user->role === 'Manager') {
            return ['User', 'Manager'];
        }

        return ['User'];
    }
}