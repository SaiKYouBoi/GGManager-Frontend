<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Models\GameMatch;
use App\Services\BracketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function __construct(private BracketService $bracketService)
    {

    }

    /**
     * @OA\Patch(
     *     path="/matches/{match}/score",
     *     tags={"Matches"},
     *     summary="Update match score and advance winner (organizer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="match", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"score_player1","score_player2","winner_id"},
     *             @OA\Property(property="score_player1", type="integer"),
     *             @OA\Property(property="score_player2", type="integer"),
     *             @OA\Property(property="winner_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Score updated"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function updateScore(Request $request, GameMatch $match): JsonResponse
    {
        $this->authorize('updateScore', $match);

        $validated = $request->validate([
            'score_player1' => 'required|integer|min:0',
            'score_player2' => 'required|integer|min:0',
            'winner_id' => 'required|exists:users,id',
        ]);

        // winner must be one of the two players
        if (
            !in_array($validated['winner_id'], [
                $match->player1_id,
                $match->player2_id,
            ])
        ) {
            return response()->json([
                'message' => 'winner_id must be player1 or player2 of this match.'
            ], 422);
        }

        if ($validated['score_player1'] === $validated['score_player2']) {
            return response()->json([
                'message' => 'Scores cannot be equal. There must be one winner.'
            ], 422);
        }

        $match->update([
            'score_player1' => $validated['score_player1'],
            'score_player2' => $validated['score_player2'],
            'winner_id' => $validated['winner_id'],
            'status' => 'finished',
        ]);

        $this->bracketService->advanceWinner($match);

         broadcast(new ScoreUpdated($match));

        return response()->json([
            'message' => 'Score updated.',
            'match' => $match->fresh()->load('player1', 'player2', 'winner'),
        ]);
    }
}
