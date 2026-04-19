<?php

namespace App\Jobs;

use App\Models\Tournament;
use App\Services\BracketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBracketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public int $tries = 1;

    public function __construct(public Tournament $tournament)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(BracketService $bracketService): void
    {
        Log::info("GenerateBracketJob started for tournament [{$this->tournament->id}]");

        $bracketService->generate($this->tournament);

        Log::info("GenerateBracketJob completed for tournament [{$this->tournament->id}]");
    }

    /**
     * If the job fails, log it clearly.
     * You could also notify the organizer here via a notification/email.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateBracketJob FAILED for tournament [{$this->tournament->id}]: {$exception->getMessage()}");
    }
}
