<?php

namespace App\Events;

use App\Models\GameMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public GameMatch $match)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tournament.' . $this->match->tournament_id);
    }

    /**
     * The data sent to the client.
     */
    public function broadcastWith(): array
    {
        return [
            'match_id' => $this->match->id,
            'round' => $this->match->round,
            'position' => $this->match->position,
            'player1' => [
                'id' => $this->match->player1->id,
                'name' => $this->match->player1->name,
                'score' => $this->match->score_player1,
            ],
            'player2' => [
                'id' => $this->match->player2->id,
                'name' => $this->match->player2->name,
                'score' => $this->match->score_player2,
            ],
            'winner_id' => $this->match->winner_id,
            'tournament_status' => $this->match->tournament->status,
        ];
    }

    /**
     * The event name the client listens for.
     */
    public function broadcastAs(): string
    {
        return 'score.updated';
    }

}
