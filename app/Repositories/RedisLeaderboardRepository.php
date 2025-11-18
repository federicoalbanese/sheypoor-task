<?php

namespace App\Repositories;

use App\Contracts\Repositories\LeaderboardRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class RedisLeaderboardRepository implements LeaderboardRepositoryInterface
{
    protected string $key;

    public function __construct(string $key = 'leaderboard')
    {
        $this->key = $key;
    }

    /**
     * @param int $userId
     * @param float $score
     *
     * @return bool
     */
    public function addScore(int $userId, float $score): bool
    {
        return (bool) Redis::zadd($this->key, $score, $userId);
    }

    /**
     * @param int $userId
     * @param float $points
     *
     * @return float
     */
    public function incrementScore(int $userId, float $points): float
    {
        return (float) Redis::zincrby($this->key, $points, $userId);
    }

    /**
     * @param int $userId
     *
     * @return int|null
     */
    public function getUserRank(int $userId): ?int
    {
        $rank = Redis::zrevrank($this->key, $userId);

        return $rank !== false ? $rank + 1 : null;
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getTopUsers(int $limit = 10): array
    {
        $data = Redis::zrevrange($this->key, 0, $limit - 1, ['WITHSCORES' => true]);

        return $this->formatLeaderboard($data);
    }

    /**
     * @param int $userId
     * @return float|null
     */
    public function getUserScore(int $userId): ?float
    {
        $score = Redis::zscore($this->key, $userId);

        return $score !== false ? (float) $score : null;
    }

    protected function formatLeaderboard(array $data): array
    {
        $result = [];
        $rank = 1;

        foreach ($data as $userId => $score) {
            $result[] = [
                'rank' => $rank++,
                'user_id' => (int) $userId,
                'score' => (float) $score,
            ];
        }

        return $result;
    }
}
