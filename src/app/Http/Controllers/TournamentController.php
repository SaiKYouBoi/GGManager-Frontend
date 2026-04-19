<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Http\Requests\StoreTournamentRequest;
use App\Http\Requests\UpdateTournamentRequest;
use App\Services\BracketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TournamentController extends Controller
{
    public function __construct(private BracketService $bracketService)
    {

    }

    /**
     * @OA\Get(
     *     path="/tournaments",
     *     tags={"Tournaments"},
     *     summary="List all tournaments",
     *     @OA\Parameter(name="game", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"open","closed","completed"})),
     *     @OA\Response(response=200, description="List of tournaments")
     * )
     */
    public function index(): JsonResponse
    {
        $tournaments = Tournament::query()
            ->when(request('game'), fn($q, $game) => $q->where('game', $game))
            ->when(request('status'), fn($q, $status) => $q->where('status', $status))
            ->get();

        return response()->json($tournaments);
    }

    /**
     * @OA\Post(
     *     path="/tournaments",
     *     tags={"Tournaments"},
     *     summary="Create a tournament (organizer only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","game","max_participants"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="game", type="string"),
     *             @OA\Property(property="max_participants", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Tournament created"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreTournamentRequest $request): JsonResponse
    {
        $this->authorize('create', Tournament::class);

        $tournament = Tournament::create(
            $request->validated() + ['organizer_id' => Auth::user()->id, 'status' => 'open']
        );


        return response()->json($tournament, 201);
    }

    /**
     * @OA\Get(
     *     path="/tournaments/{id}",
     *     tags={"Tournaments"},
     *     summary="Get a tournament",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Tournament details"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Tournament $tournament): JsonResponse
    {
        return response()->json(
            $tournament->load('organizer', 'matches')
        );
    }

    /**
     * @OA\Put(
     *     path="/tournaments/{id}",
     *     tags={"Tournaments"},
     *     summary="Update a tournament (organizer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="game", type="string"),
     *             @OA\Property(property="max_participants", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tournament updated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateTournamentRequest $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $tournament->update($request->validated());

        return response()->json($tournament);
    }

    /**
     * @OA\Delete(
     *     path="/tournaments/{id}",
     *     tags={"Tournaments"},
     *     summary="Delete a tournament (organizer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Tournament $tournament): JsonResponse
    {
        $this->authorize('delete', $tournament);

        $tournament->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/tournaments/{id}/bracket",
     *     tags={"Tournaments"},
     *     summary="Get tournament bracket",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Bracket tree"),
     *     @OA\Response(response=422, description="Bracket not generated yet")
     * )
     */
    public function bracket(Tournament $tournament): JsonResponse
    {
        if ($tournament->status === 'open') {
            return response()->json([
                'message' => 'Bracket not generated yet. Registrations are still open.'
            ], 422);
        }

        $bracket = $this->bracketService->getBracketTree($tournament);

        return response()->json([
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'game' => $tournament->game,
                'status' => $tournament->status,
            ],
            'rounds' => $bracket,
        ]);
    }

}
