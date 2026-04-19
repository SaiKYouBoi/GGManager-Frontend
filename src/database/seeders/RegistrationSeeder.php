<?php

namespace Database\Seeders;

use App\Models\Registration;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tournaments = Tournament::all();

        if ($users->isEmpty() || $tournaments->isEmpty()) {
            $this->command->warn('creat table users ant tournaments first!');
            return;
        }

        for ($i = 0; $i < 10;$i++) {
            $user = $users->random();
            $tournament = $tournaments->random();

            $exists = Registration::where('user_id', $user->id)
                                  ->where('tournament_id', $tournament->id)
                                  ->exists();

            if (!$exists) {
                Registration::create([
                    'user_id' => $user->id,
                    'tournament_id' => $tournament->id,
                    'registered_at' => now()->subDays(rand(1, 5)),
                    'status' => 'confirmed',
                ]);
            }
        }

        $this->command->info('Registrations seedées with succees!');
    }
}