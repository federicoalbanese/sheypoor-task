<?php

namespace App\Services;

use App\Contracts\Models\UserInterface;
use App\Contracts\Repositories\LeaderboardRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\Leaderboard\AwardPointsRequest;
use App\DTOs\Leaderboard\AwardPointsResult;
use App\DTOs\Leaderboard\Leaderboard;
use App\DTOs\Leaderboard\TransactionDTO;
use App\DTOs\Leaderboard\TransactionHistory;
use App\Jobs\SavePointsToDatabase;
use App\Models\LeaderboardScore;
use App\Models\NullUser;
use App\Models\PointTransaction;

class LeaderboardService
{
    public function __construct(
        protected LeaderboardRepositoryInterface $repository,
        protected UserRepositoryInterface $userRepository
    ) {}

    public function awardPoints(AwardPointsRequest $request): AwardPointsResult
    {
        $newScore = $this->repository->incrementScore($request->userId, $request->points);
        $rank = $this->repository->getUserRank($request->userId);

        dispatch(new SavePointsToDatabase(
            userId: $request->userId,
            points: $request->points,
            source: $request->source,
            metadata: $request->metadata
        ))->afterResponse();

        return new AwardPointsResult(
            success: true,
            transactionId: 0,
            newScore: $newScore,
            pointsAwarded: $request->points,
            source: $request->source,
            rank: $rank
        );
    }

    public function getUserRank(int $userId): ?int
    {
        return $this->repository->getUserRank($userId);
    }

    public function getUserScore(int $userId): ?float
    {
        return $this->repository->getUserScore($userId);
    }

    public function getUserById(int $userId): UserInterface
    {
        $user = $this->userRepository->findById($userId);

        return $user ?? NullUser::create();
    }

    public function getTopUsers(int $limit = 100): Leaderboard
    {
        try {
            $leaderboard = $this->repository->getTopUsers($limit);
            $leaderboardData = $this->enrichWithUserData($leaderboard);
        } catch (\Exception $e) {
            $leaderboardData = $this->getLeaderboardFromDatabase($limit);
        }

        return Leaderboard::create(
            $leaderboardData,
            now()->toIso8601String()
        );
    }

    protected function enrichWithUserData(array $leaderboard): array
    {
        if (empty($leaderboard)) {
            return [];
        }

        $userIds = array_column($leaderboard, 'user_id');
        $users = $this->userRepository->findByIds($userIds);

        $usernameMap = [];
        foreach ($users as $user) {
            $usernameMap[$user->getId()] = $user->getUsername();
        }

        foreach ($leaderboard as &$entry) {
            $entry['username'] = $usernameMap[$entry['user_id']] ?? 'Unknown';
        }

        return $leaderboard;
    }

    protected function getLeaderboardFromDatabase(int $limit): array
    {
        return LeaderboardScore::with('user')
            ->orderByDesc('score')
            ->limit($limit)
            ->get()
            ->map(fn ($row, $idx) => [
                'rank' => $idx + 1,
                'user_id' => $row->user_id,
                'username' => $row->user?->username ?? 'Unknown',
                'score' => (float) $row->score,
            ])
            ->toArray();
    }

    public function getUserPointHistory(int $userId, int $limit = 50): TransactionHistory
    {
        $transactions = PointTransaction::where('user_id', $userId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn($tx) => TransactionDTO::fromModel($tx))
            ->toArray();

        return TransactionHistory::create($userId, $transactions);
    }
}
