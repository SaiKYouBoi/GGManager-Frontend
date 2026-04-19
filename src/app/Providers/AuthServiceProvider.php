<?php

namespace App\Providers;

use App\Models\GameMatch;
use App\Models\Tournament;
use App\Policies\TournamentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Tournament::class, TournamentPolicy::class);
        Gate::policy(GameMatch::class, \App\Policies\MatchPolicy::class);
    }
}
