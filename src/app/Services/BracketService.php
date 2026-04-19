<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Match;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class BracketService
{
    public function generate(Tournament $tournament)
    {
        DB::transaction(function () use ($tournament) {

            $players = $tournament->registrations()
                ->where('status', 'confirmed')
                ->with('user')
                ->get()
                ->pluck('user');

            if ($players->count() < 2) {
                throw new \RuntimeException(
                    "Cannot generate bracket: tournament [{$tournament->id}] has fewer than 2 confirmed players."
                );
            }

            $players = $players->shuffle();

            $bracketSize = $this->nextPowerOfTwo($players->count());
            $totalRounds = (int) log($bracketSize, 2);
            $byeCount = $bracketSize - $players->count();

            $this->buildRoundOne($tournament, $players, $bracketSize, $byeCount);

            $this->buildPlaceholderRounds($tournament, $totalRounds);

            $this->advanceByeWinners($tournament);
        });
    }

    private function distributeSlots(array $players, int $bracketSize): array
    {
        $slots = array_fill(0, $bracketSize, null);
        $byeCount = $bracketSize - count($players);
        // Place byes at even-indexed positions (slot 1 of each pair) spread across pairs
        $byePositions = [];
        $step = $bracketSize / max($byeCount, 1);
        for ($i = 0; $i < $byeCount; $i++) {
            $byePositions[] = (int)($i * $step) * 2 + 1; // odd index = slot[1] of pair
        }
        $playerIdx = 0;
        for ($i = 0; $i < $bracketSize; $i++) {
            if (!in_array($i, $byePositions)) {
                $slots[$i] = $players[$playerIdx++] ?? null;
            }
        }
        return $slots;
    }

    private function nextPowerOfTwo(int $n): int
    {
        $power = 1;
        while ($power < $n) {
            $power *= 2;
        }
        return $power;
    }

    private function buildRoundOne(
        Tournament $tournament,
        Collection $players,
        int        $bracketSize,
        int        $byeCount
    ): void {

        $slots = $this->distributeSlots($players->values()->all(), $bracketSize);

        $position = 1;


        for ($i = 0; $i < $bracketSize; $i += 2) {
            $player1 = $slots[$i];
            $player2 = $slots[$i + 1];

            if ($player1 === null) [$player1, $player2] = [$player2, $player1];
            $isBye = $player2 === null;

            GameMatch::create([
                'tournament_id' => $tournament->id,
                'round'         => 1,
                'position'      => $position,
                'player1_id'    => $player1?->id,
                'player2_id'    => $player2?->id,    // NULL for bye
                'winner_id'     => $isBye ? $player1->id : null,
                'score_player1' => 0,
                'score_player2' => 0,
                'status'        => $isBye ? 'finished' : 'pending',
            ]);

            $position++;
        }
    }

    private function buildPlaceholderRounds(Tournament $tournament, int $totalRounds): void
    {
        for ($round = 2; $round <= $totalRounds; $round++) {

            $matchCount = (int) ($this->bracketSizeForRound($round, $totalRounds));

            for ($position = 1; $position <= $matchCount; $position++) {
                GameMatch::create([
                    'tournament_id' => $tournament->id,
                    'round'         => $round,
                    'position'      => $position,
                    'player1_id'    => null,
                    'player2_id'    => null,
                    'winner_id'     => null,
                    'score_player1' => 0,
                    'score_player2' => 0,
                    'status'        => 'pending',
                ]);
            }
        }
    }

    private function bracketSizeForRound(int $round, int $totalRounds): int
    {
        return (int) (2 ** $totalRounds / 2 ** $round);
    }

    public function advanceWinner(GameMatch $match): void
    {

        $nextRound    = $match->round + 1;
        $nextPosition = (int) ceil($match->position / 2);
        $slot         = $match->position % 2 === 1 ? 'player1_id' : 'player2_id';

        $nextMatch = GameMatch::where('tournament_id', $match->tournament_id)
            ->where('round',    $nextRound)
            ->where('position', $nextPosition)
            ->first();

        if ($nextMatch) {

            $nextMatch->update([$slot => $match->winner_id]);
        } else {

            $match->tournament->update(['status' => 'completed']);
        }
    }

     private function advanceByeWinners(Tournament $tournament): void
    {
        $byeMatches = GameMatch::where('tournament_id', $tournament->id)
            ->where('round', 1)
            ->where('status', 'finished')   // only byes are finished at this point
            ->whereNotNull('winner_id')
            ->get();

        foreach ($byeMatches as $byeMatch) {
            $this->advanceWinner($byeMatch);
        }
    }

    public function getBracketTree(Tournament $tournament): array
    {
        $matches = GameMatch::where('tournament_id', $tournament->id)
            ->with(['player1', 'player2', 'winner'])
            ->orderBy('round')
            ->orderBy('position')
            ->get();


        return $matches
            ->groupBy('round')
            ->map(fn (Collection $roundMatches) =>
                $roundMatches->map(fn (GameMatch $m) => [
                    'id'           => $m->id,
                    'round'        => $m->round,
                    'position'     => $m->position,
                    'player1'      => $m->player1 ? ['id' => $m->player1->id, 'name' => $m->player1->name] : null,
                    'player2'      => $m->player2 ? ['id' => $m->player2->id, 'name' => $m->player2->name] : null,
                    'winner'       => $m->winner  ? ['id' => $m->winner->id,  'name' => $m->winner->name]  : null,
                    'score_player1'=> $m->score_player1,
                    'score_player2'=> $m->score_player2,
                    'status'       => $m->status,
                ])->values()
            )
            ->toArray();
    }

}