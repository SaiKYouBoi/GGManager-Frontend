<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateBracketJob;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/tournaments/{tournament}/register",
     *     tags={"Registrations"},
     *     summary="Register current player to a tournament",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="tournament", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request, Tournament $tournament): JsonResponse
    {
        if ($tournament->status !== 'open') {
            return response()->json(['message' => 'Tournament is not open for registration.'], 422);
        }

        $confirmed = $tournament->registrations()->where('status', 'confirmed')->count();
        if ($confirmed >= $tournament->max_participants) {
            return response()->json(['message' => 'Tournament is full.'], 422);
        }

        $already = $tournament->registrations()
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($already) {
            return response()->json(['message' => 'You are already registered for this tournament.'], 422);
        }

        $tournament->registrations()->create([
            'user_id' => $request->user()->id,
            'registered_at' => now(),
            'status' => 'confirmed',
        ]);

        return response()->json(['message' => 'Registered successfully.'], 201);
    }

    /**
     * @OA\Get(
     *     path="/tournaments/{tournament}/registrations",
     *     tags={"Registrations"},
     *     summary="List confirmed registrations (organizer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="tournament", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of registered players"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Tournament $tournament): JsonResponse
    {
        $this->authorize('manage', $tournament);

        $players = $tournament->registrations()
            ->where('status', 'confirmed')
            ->with('user:id,name,email')
            ->get()
            ->map(fn($reg) => [
                'id' => $reg->id,
                'player' => $reg->user,
                'registered_at' => $reg->registered_at,
                'status' => $reg->status,
            ]);

        return response()->json($players);
    }

    /**
     * @OA\Patch(
     *     path="/tournaments/{tournament}/close",
     *     tags={"Registrations"},
     *     summary="Close registrations and generate bracket (organizer only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="tournament", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Registrations closed"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function close(Tournament $tournament): JsonResponse
    {
        $this->authorize('manage', $tournament);

        if ($tournament->status !== 'open') {
            return response()->json(['message' => 'Tournament is not open.'], 422);
        }

        $confirmedCount = $tournament->registrations()
            ->where('status', 'confirmed')
            ->count();

        if ($confirmedCount < 2) {
            return response()->json(['message' => 'Need at least 2 confirmed players to close registrations.'], 422);
        }

        $tournament->update(['status' => 'closed']);

        GenerateBracketJob::dispatch($tournament);

        return response()->json([
            'message' => 'Registrations closed. Bracket generation started.',
        ]);
    }
}