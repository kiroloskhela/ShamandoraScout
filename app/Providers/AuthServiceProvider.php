<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Policies\CurriculaPolicy;
use App\Policies\CustodyPolicy;
use App\Policies\EnrolmentPolicy;
use App\Policies\EventBookingPolicy;
use App\Policies\GamePolicy;
use App\Policies\MedicinePolicy;
use App\Policies\PersonPolicy;
use App\Policies\TreePolicy;
use App\Policies\WhatsAppCampaignPolicy;
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
        WhatsAppCampaign::class => WhatsAppCampaignPolicy::class,
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

        Gate::define('eventBooking.create', fn (?User $user) => $user !== null && (new EventBookingPolicy)->create($user));
        Gate::define('eventBooking.update', fn (?User $user) => $user !== null && (new EventBookingPolicy)->update($user));
        Gate::define('eventBooking.delete', fn (?User $user) => $user !== null && (new EventBookingPolicy)->delete($user));

        Gate::define('tree.manageQetaa', fn (?User $user, int $qetaaId) => $user !== null && (new TreePolicy)->manageQetaa($user, $qetaaId));
        Gate::define('tree.manageGroup', fn (?User $user, int $groupId) => $user !== null && (new TreePolicy)->manageGroup($user, $groupId));

        Gate::define('medicine.viewAny', fn (?User $user) => $user !== null && (new MedicinePolicy)->viewAny($user));
        Gate::define('medicine.manage', fn (?User $user) => $user !== null && (new MedicinePolicy)->manage($user));
        Gate::define('medicine.dispense', fn (?User $user) => $user !== null && (new MedicinePolicy)->dispense($user));

        Gate::define('custody.create', fn (?User $user) => $user !== null && (new CustodyPolicy)->create($user));
        Gate::define('custody.view', fn (?User $user, int $requestId) => $user !== null && (new CustodyPolicy)->view($user, $requestId));
        Gate::define('custody.update', fn (?User $user, int $requestId) => $user !== null && (new CustodyPolicy)->update($user, $requestId));
        Gate::define('custody.delete', fn (?User $user, int $requestId) => $user !== null && (new CustodyPolicy)->delete($user, $requestId));
        Gate::define('custody.viewAdmin', fn (?User $user) => $user !== null && (new CustodyPolicy)->viewAdmin($user));
        Gate::define('custody.review', fn (?User $user) => $user !== null && (new CustodyPolicy)->review($user));

        Gate::define('enrolment.viewAny', fn (?User $user) => $user !== null && (new EnrolmentPolicy)->viewAny($user));
        Gate::define('enrolment.view', fn (?User $user, int $id) => $user !== null && (new EnrolmentPolicy)->view($user, $id));
        Gate::define('enrolment.update', fn (?User $user, int $id) => $user !== null && (new EnrolmentPolicy)->update($user, $id));
        Gate::define('enrolment.approve', fn (?User $user, int $id) => $user !== null && (new EnrolmentPolicy)->approve($user, $id));
        Gate::define('enrolment.delete', fn (?User $user, int $id) => $user !== null && (new EnrolmentPolicy)->delete($user, $id));
        Gate::define('enrolment.migrate', fn (?User $user) => $user !== null && (new EnrolmentPolicy)->migrate($user));
    }
}
