<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Role-based Gates
         * Hierarchical roles: SuperAdmin > Admin > Manager > User
         */
        Gate::define('superAdmin', function (User $user) {
            return $user->role === 'SuperAdmin';
        });

        Gate::define('admin', function (User $user) {
            return in_array($user->role, ['Admin', 'SuperAdmin']);
        });

        Gate::define('manager', function (User $user) {
            return in_array($user->role, ['Manager', 'Admin', 'SuperAdmin']);
        });

        Gate::define('user', function (User $user) {
            return in_array($user->role, ['User', 'Manager', 'Admin', 'SuperAdmin']);
        });

        /**
         * User Management Gates
         */
        Gate::define('manage-users', function (User $user) {
            return in_array($user->role, ['Manager', 'Admin', 'SuperAdmin']);
        });

        Gate::define('manage-user', function (User $user, User $targetUser) {
            // Define role hierarchy levels
            $roleHierarchy = [
                'User' => 1,
                'Manager' => 2,
                'Admin' => 3,
                'SuperAdmin' => 4,
            ];

            $userLevel = $roleHierarchy[$user->role] ?? 0;
            $targetLevel = $roleHierarchy[$targetUser->role] ?? 0;

            // Can only manage users with lower role level
            return $userLevel > $targetLevel;
        });

        /**
         * Receipt Management Gates
         */
        Gate::define('scan-receipts', function (User $user) {
            return true; // All authenticated users can scan receipts
        });

        Gate::define('view-own-receipts', function (User $user) {
            return true; // All authenticated users can view their own receipts
        });

        Gate::define('view-all-receipts', function (User $user) {
            return in_array($user->role, ['Admin', 'SuperAdmin']);
        });
    }

    /**
     * Get assignable roles for a user based on their current role
     */
    public static function getAssignableRoles(?User $user): array
    {
        if (!$user) {
            return [];
        }

        // Define role hierarchy levels
        $roleHierarchy = [
            'User' => 1,
            'Manager' => 2,
            'Admin' => 3,
            'SuperAdmin' => 4,
        ];

        $userLevel = $roleHierarchy[$user->role] ?? 0;
        $assignableRoles = [];

        foreach ($roleHierarchy as $role => $level) {
            // Can assign roles with level <= own level
            if ($level <= $userLevel) {
                $assignableRoles[] = $role;
            }
        }

        return $assignableRoles;
    }
}