<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->info('users not found! Run UserSeeder first.');
            return;
        }

        $games = ['League of Legends', 'Valorant', 'FIFA 26', 'Counter-Strike 2', 'Rocket League'];

        for ($i = 0; $i < 10; $i++) {
            Tournament::create([
                'name' => 'Tournament ' . ($i + 1) . ' - ' . $games[array_rand($games)],
                'game' => $games[array_rand($games)],
                'date' => now()->addDays(rand(1, 30)), 
                'max_participants' => [8, 16, 32, 64][rand(0, 3)],
                'status' => 'open',
                'format' => 'single_elimination',
                'organizer_id' => $userIds[array_rand($userIds)],
            ]);
        }
    }
}