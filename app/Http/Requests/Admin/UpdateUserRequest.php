<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $targetUser = $this->route('user');

        if (!$targetUser) {
            return false;
        }

        return $this->user()->can('manage-user', $targetUser);
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $availableRoles = $this->getAvailableRoles();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($availableRoles)],
        ];
    }

    private function getAvailableRoles(): array
    {
        return \App\Providers\AuthServiceProvider::getAssignableRoles($this->user());
    }
}