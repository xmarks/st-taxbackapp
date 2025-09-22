<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RoleBadge extends Component
{
    public function __construct(
        public string $role
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.role-badge');
    }

    public function roleClasses(): string
    {
        return match ($this->role) {
            'SuperAdmin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            'Admin' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'Manager' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            default => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        };
    }
}