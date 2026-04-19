<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tournament.{tournamentId}', function () {
    return true;
});
