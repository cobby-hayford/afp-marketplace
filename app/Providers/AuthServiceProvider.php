<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->role === 'super_administrator' ? true : null;
        });
        Gate::define('access-admin', fn (User $u) => in_array($u->role, ['moderator', 'administrator'], true));
        Gate::define('manage-admin-roles', fn (User $u) => $u->role === 'super_administrator');
    }
}
