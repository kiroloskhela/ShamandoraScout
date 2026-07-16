<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\User;
use App\Policies\CurriculaPolicy;
use App\Policies\GamePolicy;
use App\Policies\PersonPolicy;
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
        User::class => PersonPolicy::class,
        Game::class => GamePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Keep string abilities used by GamesApiController; delegate to GamePolicy.
        Gate::define('games.view', fn (?User $user) => $user !== null && $user->can('viewAny', Game::class));
        Gate::define('games.create', fn (?User $user) => $user !== null && $user->can('create', Game::class));
        Gate::define('games.update', function (?User $user) {
            return $user !== null && (new GamePolicy)->create($user);
        });
        Gate::define('games.delete', function (?User $user) {
            return $user !== null && (new GamePolicy)->create($user);
        });

        Gate::define('curricula.view', fn (?User $user) => $user !== null && (new CurriculaPolicy)->viewAny($user));
        Gate::define('curricula.create', fn (?User $user) => $user !== null && (new CurriculaPolicy)->create($user));
        Gate::define('curricula.update', fn (?User $user) => $user !== null && (new CurriculaPolicy)->update($user));
        Gate::define('curricula.delete', fn (?User $user) => $user !== null && (new CurriculaPolicy)->delete($user));
    }
}
