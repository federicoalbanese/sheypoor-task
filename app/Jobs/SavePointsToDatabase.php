<?php

namespace App\Jobs;

use App\Contracts\Repositories\LeaderboardRepositoryInterface;
use App\Models\FailedLeaderboardSync;
use App\Models\LeaderboardScore;
use App\Models\PointTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SavePointsToDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [10, 30, 60, 120];
    public $timeout = 30;

    public function __construct(
        public int $userId,
        public float $points,
        public string $source,
        public array $metadata = []
    ) {}

    public function handle(
        LeaderboardRepositoryInterface $repository
    ): void {
        $transaction = PointTransaction::create([
            'user_id' => $this->userId,
            'points' => $this->points,
            'source' => $this->source,
            'metadata' => $this->metadata,
        ]);

        $redisScore = $repository->getUserScore($this->userId);

        if ($redisScore === null) {
            return;
        }

        $leaderboardScore = LeaderboardScore::firstOrNew(['user_id' => $this->userId]);
        $leaderboardScore->score = $redisScore;
        $leaderboardScore->last_transaction_id = $transaction->id;
        $leaderboardScore->save();
    }

    public function failed(\Throwable $exception): void
    {
        FailedLeaderboardSync::create([
            'user_id' => $this->userId,
            'transaction_id' => 0,
            'error' => $exception->getMessage(),
            'failed_at' => now(),
        ]);

        Log::error('Database sync permanently failed', [
            'user_id' => $this->userId,
            'points' => $this->points,
            'source' => $this->source,
            'error' => $exception->getMessage(),
        ]);
    }
}
