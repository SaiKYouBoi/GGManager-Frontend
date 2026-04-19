<?php

namespace Tests\Unit;

use App\Models\GameMatch;
use App\Models\Match;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Registration;
use App\Services\BracketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BracketServiceTest extends TestCase
{
    use RefreshDatabase;

    private BracketService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BracketService();
    }

    // -------------------------------------------------------------------------
    //  Helper — creates a tournament with N confirmed players
    // -------------------------------------------------------------------------

    private function makeTournament(int $playerCount): Tournament
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $tournament = Tournament::factory()->create([
            'organizer_id' => $organizer->id,
            'max_participants' => $playerCount,
            'status' => 'closed',
        ]);

        User::factory($playerCount)->create(['role' => 'player'])
            ->each(
                fn($player) =>
                Registration::factory()->create([
                    'tournament_id' => $tournament->id,
                    'user_id' => $player->id,
                    'status' => 'confirmed',
                ])
            );

        return $tournament;
    }


    // -------------------------------------------------------------------------
    //  generate() — standard power-of-2 cases
    // -------------------------------------------------------------------------

    public function test_it_generates_correct_rounds_for_4_players(): void
    {
        $tournament = $this->makeTournament(4);
        $this->service->generate($tournament);

        // 4 players → 2 rounds → 2 + 1 = 3 matches total
        $this->assertEquals(3, GameMatch::where('tournament_id', $tournament->id)->count());

        // Round 1 must have 2 GameMatches
        $this->assertEquals(2, GameMatch::where('tournament_id', $tournament->id)->where('round', 1)->count());

        // Round 2 (final) must have 1 GameMatch
        $this->assertEquals(1, GameMatch::where('tournament_id', $tournament->id)->where('round', 2)->count());
    }

    public function test_it_generates_correct_rounds_for_8_players(): void
    {
        $tournament = $this->makeTournament(8);
        $this->service->generate($tournament);

        // 8 players → 3 rounds → 4 + 2 + 1 = 7 matches total
        $this->assertEquals(7, GameMatch::where('tournament_id', $tournament->id)->count());

        $this->assertEquals(4, GameMatch::where('tournament_id', $tournament->id)->where('round', 1)->count());
        $this->assertEquals(2, GameMatch::where('tournament_id', $tournament->id)->where('round', 2)->count());
        $this->assertEquals(1, GameMatch::where('tournament_id', $tournament->id)->where('round', 3)->count());
    }

    public function test_it_generates_correct_rounds_for_16_players(): void
    {
        $tournament = $this->makeTournament(16);
        $this->service->generate($tournament);

        // 16 players → 4 rounds → 8 + 4 + 2 + 1 = 15 matches total
        $this->assertEquals(15, GameMatch::where('tournament_id', $tournament->id)->count());
    }

    public function test_all_round_one_matches_have_two_players_for_power_of_two(): void
    {
        $tournament = $this->makeTournament(4);
        $this->service->generate($tournament);

        $round1 = GameMatch::where('tournament_id', $tournament->id)->where('round', 1)->get();

        foreach ($round1 as $match) {
            $this->assertNotNull($match->player1_id, 'player1 should never be null in round 1');
            $this->assertNotNull($match->player2_id, 'player2 should not be null when no byes needed');
        }
    }

    public function test_placeholder_matches_have_null_players(): void
    {
        $tournament = $this->makeTournament(4);
        $this->service->generate($tournament);

        $finalMatch = GameMatch::where('tournament_id', $tournament->id)->where('round', 2)->first();

        $this->assertNull($finalMatch->player1_id);
        $this->assertNull($finalMatch->player2_id);
        $this->assertNull($finalMatch->winner_id);
        $this->assertEquals('pending', $finalMatch->status);
    }


    // -------------------------------------------------------------------------
    //  generate() — bye / non-power-of-2 cases
    // -------------------------------------------------------------------------

    public function test_it_handles_5_players_with_byes(): void
    {
        $tournament = $this->makeTournament(5);
        $this->service->generate($tournament);

        // 5 players → bracket size 8 → 3 rounds → 4 + 2 + 1 = 7 matches
        $this->assertEquals(7, GameMatch::where('tournament_id', $tournament->id)->count());

        // 3 of the 4 round-1 matches should be real (pending),
        // 1 should be a bye (finished, winner already set)
        $byeMatches = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 1)
            ->where('status', 'finished')
            ->count();

        $this->assertEquals(3, $byeMatches, 'Expected 3 bye matches for 5 players (bracket size 8)');
    }

    public function test_bye_winner_is_advanced_to_round_two(): void
    {
        $tournament = $this->makeTournament(5);
        $this->service->generate($tournament);

        // Find the bye match
        $byeMatch = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 1)
            ->where('status', 'finished')
            ->first();

        $this->assertNotNull($byeMatch->winner_id, 'Bye match must have a winner set');

        // That winner should appear in a round-2 match
        $inRound2 = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 2)
            ->where(function ($q) use ($byeMatch) {
                $q->where('player1_id', $byeMatch->winner_id)
                    ->orWhere('player2_id', $byeMatch->winner_id);
            })
            ->exists();

        $this->assertTrue($inRound2, 'Bye winner should be pre-placed in round 2');
    }

    public function test_it_handles_3_players_with_one_bye(): void
    {
        $tournament = $this->makeTournament(3);
        $this->service->generate($tournament);

        // 3 players → bracket size 4 → 2 rounds → 2 + 1 = 3 matches
        $this->assertEquals(3, GameMatch::where('tournament_id', $tournament->id)->count());

        $byeCount = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 1)
            ->where('status', 'finished')
            ->count();

        $this->assertEquals(1, $byeCount);
    }


    // -------------------------------------------------------------------------
    //  advanceWinner()
    // -------------------------------------------------------------------------

    public function test_winner_is_placed_in_correct_next_round_slot(): void
    {
        $tournament = $this->makeTournament(4);
        $this->service->generate($tournament);

        // Manually finish match at round=1, position=1
        $match1 = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 1)->where('position', 1)->first();

        $match1->update([
            'winner_id' => $match1->player1_id,
            'score_player1' => 3,
            'score_player2' => 1,
            'status' => 'finished',
        ]);

        $this->service->advanceWinner($match1);

        // ceil(1/2) = 1, position is odd → player1_id slot
        $final = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 2)->where('position', 1)->first();

        $this->assertEquals($match1->player1_id, $final->player1_id);
    }

    public function test_winner_from_even_position_fills_player2_slot(): void
    {
        $tournament = $this->makeTournament(4);
        $this->service->generate($tournament);

        // Manually finish match at round=1, position=2
        $match2 = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 1)->where('position', 2)->first();

        $match2->update([
            'winner_id' => $match2->player2_id,
            'score_player1' => 0,
            'score_player2' => 3,
            'status' => 'finished',
        ]);

        $this->service->advanceWinner($match2);

        // ceil(2/2) = 1, position is even → player2_id slot
        $final = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 2)->where('position', 1)->first();

        $this->assertEquals($match2->player2_id, $final->player2_id);
    }

    public function test_tournament_is_marked_completed_after_final(): void
    {
        $tournament = $this->makeTournament(2);
        $this->service->generate($tournament);

        $final = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 1)->where('position', 1)->first();

        $final->update([
            'winner_id' => $final->player1_id,
            'score_player1' => 3,
            'score_player2' => 0,
            'status' => 'finished',
        ]);

        $this->service->advanceWinner($final);

        $this->assertEquals('completed', $tournament->fresh()->status);
    }


    // -------------------------------------------------------------------------
    //  Edge cases
    // -------------------------------------------------------------------------

    public function test_it_throws_if_fewer_than_2_players(): void
    {
        $this->expectException(\RuntimeException::class);

        $tournament = $this->makeTournament(1);
        $this->service->generate($tournament);
    }

    public function test_each_round_position_is_unique_within_tournament(): void
    {
        $tournament = $this->makeTournament(8);
        $this->service->generate($tournament);

        $matches = GameMatch::where('tournament_id', $tournament->id)->get();

        $combos = $matches->map(fn($m) => "{$m->round}-{$m->position}");

        // No duplicates
        $this->assertEquals($combos->count(), $combos->unique()->count());
    }
}
