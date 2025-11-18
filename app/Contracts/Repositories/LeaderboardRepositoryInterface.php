<?php

namespace App\Contracts\Repositories;

interface LeaderboardRepositoryInterface
{
    /**
     * @param int $userId
     * @param float $score
     *
     * @return bool
     */
    public function addScore(int $userId, float $score): bool;

    /**
     * @param int $userId
     * @param float $points
     *
     * @return float The new total score
     */
    public function incrementScore(int $userId, float $points): float;

    /**
     * @param int $userId
     *
     * @return int|null
     */
    public function getUserRank(int $userId): ?int;

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getTopUsers(int $limit = 10): array;

    /**
     * @param int $userId
     *
     * @return float|null
     */
    public function getUserScore(int $userId): ?float;
}
