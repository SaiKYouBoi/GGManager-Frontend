<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TournamentPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Tournament $tournament): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'organizer';
    }

    public function update(User $user, Tournament $tournament): bool
    {
        return $user->id === $tournament->organizer_id && $tournament->status === 'open';
    }

    public function delete(User $user, Tournament $tournament): bool
    {
        return $user->id === $tournament->organizer_id && $tournament->status === 'open';
    }

    public function restore(User $user, Tournament $tournament): bool
    {
        return false;
    }

    public function forceDelete(User $user, Tournament $tournament): bool
    {
        return false;
    }

    public function manage(User $user, Tournament $tournament): bool
    {
        return $user->role === 'organizer'
            && $user->id === $tournament->organizer_id;
    }
}