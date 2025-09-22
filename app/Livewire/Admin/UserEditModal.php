<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserEditModal extends Component
{
    public $userId;
    public $showEditModal = false;

    // Form fields
    public $name = '';
    public $email = '';
    public $role = '';
    public $password = '';
    public $password_confirmation = '';

    public function mount($userId)
    {
        $this->userId = $userId;
        Gate::authorize('manage-users');
    }

    public function openEditModal()
    {
        $user = User::findOrFail($this->userId);

        if (!auth()->user()->can('manage-user', $user)) {
            session()->flash('error', 'You cannot edit this user.');
            return;
        }

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showEditModal = true;
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->role = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag();
    }

    public function updateUser()
    {
        $user = User::findOrFail($this->userId);
        $availableRoles = AuthServiceProvider::getAssignableRoles(auth()->user());

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
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

        $user->update($updateData);

        session()->flash('success', 'User updated successfully.');
        $this->closeModal();

        // Refresh the page to show updated user data
        return redirect()->route('admin.users.show', $user);
    }

    public function getAvailableRolesProperty()
    {
        return AuthServiceProvider::getAssignableRoles(auth()->user());
    }

    public function render()
    {
        return view('livewire.admin.user-edit-modal');
    }
}
