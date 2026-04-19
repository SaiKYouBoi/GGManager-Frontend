<?php

namespace Database\Seeders;

use App\Models\GameMatch; 
use App\Models\Tournament;
use App\Models\Registration;
use Illuminate\Database\Seeder;

class MatchSeeder extends Seeder
{
    public function run(): void
    {
        $tournaments = Tournament::all();

        foreach ($tournaments as $tournament) {
            $registrations = Registration::where('tournament_id', $tournament->id)
                ->where('status', 'confirmed')
                ->pluck('user_id')
                ->toArray();

            if (count($registrations) >= 2) {
                GameMatch::create([ 
                    'tournament_id'    => $tournament->id,
                    'round'            => 1,
                    'position'         => 1,
                    'player1_id'       => $registrations[0],
                    'player2_id'       => $registrations[1],
                    'score_player1'    => rand(0, 3),
                    'score_player2'    => rand(0, 3),
                    'status'           => 'finished',
                    'winner_id'        => $registrations[rand(0, 1)], // Winner random
                ]);
            }
        }

        $this->command->info('Matches seedées with succes');
    }
}