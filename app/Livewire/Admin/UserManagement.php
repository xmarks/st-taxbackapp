<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $selectedUser = null;

    // For user detail view - when set, shows only edit modal functionality
    public $detailUserId = null;

    // Form fields
    public $name = '';
    public $email = '';
    public $role = '';
    public $password = '';
    public $password_confirmation = '';

    protected $queryString = ['search'];

    public function mount()
    {
        Gate::authorize('manage-users');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($userId)
    {
        $user = User::findOrFail($userId);

        if (!auth()->user()->can('manage-user', $user)) {
            session()->flash('error', 'You cannot edit this user.');
            return;
        }

        $this->selectedUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showEditModal = true;
    }

    public function openEditModalForDetailUser()
    {
        if ($this->detailUserId) {
            $this->openEditModal($this->detailUserId);
        }
    }

    public function openDeleteModal($userId)
    {
        $user = User::findOrFail($userId);

        if (!auth()->user()->can('manage-user', $user)) {
            session()->flash('error', 'You cannot delete this user.');
            return;
        }

        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $this->selectedUser = $user;
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->role = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedUser = null;
        $this->resetErrorBag();
    }

    public function createUser()
    {
        $availableRoles = AuthServiceProvider::getAssignableRoles(auth()->user());

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:' . implode(',', $availableRoles),
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'email_verified_at' => now(),
        ]);

        session()->flash('success', 'User created successfully.');
        $this->closeModal();
    }

    public function updateUser()
    {
        $availableRoles = AuthServiceProvider::getAssignableRoles(auth()->user());

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->selectedUser->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:' . implode(',', $availableRoles),
        ]);

        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if (!empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        $this->selectedUser->update($updateData);

        session()->flash('success', 'User updated successfully.');
        $this->closeModal();
    }

    public function deleteUser()
    {
        $this->selectedUser->delete();
        session()->flash('success', 'User deleted successfully.');
        $this->closeModal();
    }

    public function getAvailableRolesProperty()
    {
        return AuthServiceProvider::getAssignableRoles(auth()->user());
    }

    public function render()
    {
        // If this is for detail view, don't show the full user list
        if ($this->detailUserId) {
            return view('livewire.admin.user-management-detail', [
                'detailUser' => User::findOrFail($this->detailUserId),
            ])->layout('layouts.app');
        }

        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ])->layout('layouts.app');
    }
}