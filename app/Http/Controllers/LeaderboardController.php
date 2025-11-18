<?php

namespace App\Http\Controllers;

use App\DTOs\Leaderboard\AwardPointsRequest as AwardPointsDTO;
use App\Http\Requests\AwardPointsRequest;
use App\Http\Resources\AwardPointsResource;
use App\Http\Resources\LeaderboardResource;
use App\Http\Resources\TransactionHistoryResource;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardController extends Controller
{
    public function __construct(
        protected LeaderboardService $leaderboard
    ) {}

    public function index(Request $request): JsonResource
    {
        $limit = (int) $request->input('limit', 100);
        $limit = min(max($limit, 1), 1000);

        $leaderboard = $this->leaderboard->getTopUsers($limit);

        return new LeaderboardResource($leaderboard);
    }

    public function show(Request $request, int $userId): JsonResponse
    {
        $rank = $this->leaderboard->getUserRank($userId);
        $score = $this->leaderboard->getUserScore($userId);
        $user = $this->leaderboard->getUserById($userId);

        return response()->json([
            'user_id' => $userId,
            'username' => $user->getUsername(),
            'rank' => $rank,
            'score' => $score ?? 0.0,
        ]);
    }

    public function award(AwardPointsRequest $request): JsonResource
    {
        $awardPointsDTO = AwardPointsDTO::fromArray($request->validated());
        $result = $this->leaderboard->awardPoints($awardPointsDTO);

        return new AwardPointsResource($result);
    }

    public function history(Request $request, int $userId): JsonResource
    {
        $limit = (int) $request->input('limit', 50);
        $limit = min(max($limit, 1), 200);

        $history = $this->leaderboard->getUserPointHistory($userId, $limit);

        return new TransactionHistoryResource($history);
    }
}
