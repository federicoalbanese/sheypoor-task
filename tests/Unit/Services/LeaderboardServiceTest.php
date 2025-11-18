<?php

namespace Tests\Unit\Services;

use App\Contracts\Repositories\LeaderboardRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\Leaderboard\AwardPointsRequest;
use App\Jobs\SavePointsToDatabase;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeaderboardServiceTest extends TestCase
{

    private LeaderboardService $service;
    private LeaderboardRepositoryInterface $leaderboardRepo;
    private UserRepositoryInterface $userRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->leaderboardRepo = $this->createMock(LeaderboardRepositoryInterface::class);
        $this->userRepo = $this->createMock(UserRepositoryInterface::class);
        $this->service = new LeaderboardService($this->leaderboardRepo, $this->userRepo);
    }

    /** @test */
    public function it_awards_points_and_returns_updated_score()
    {
        $request = new AwardPointsRequest(
            userId: 1,
            points: 50.0,
            source: 'purchase',
            metadata: []
        );

        $this->leaderboardRepo->expects($this->once())
            ->method('incrementScore')
            ->with(1, 50.0)
            ->willReturn(150.0);

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserRank')
            ->with(1)
            ->willReturn(5);

        $result = $this->service->awardPoints($request);

        $this->assertTrue($result->success);
        $this->assertEquals(150.0, $result->newScore);
        $this->assertEquals(50.0, $result->pointsAwarded);
        $this->assertEquals(5, $result->rank);
    }

    /** @test */
    public function it_handles_zero_points_award()
    {

        $request = new AwardPointsRequest(
            userId: 1,
            points: 0.0,
            source: 'test',
            metadata: []
        );

        $this->leaderboardRepo->expects($this->once())
            ->method('incrementScore')
            ->with(1, 0.0)
            ->willReturn(100.0);

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserRank')
            ->willReturn(10);


        $result = $this->service->awardPoints($request);


        $this->assertTrue($result->success);
        $this->assertEquals(0.0, $result->pointsAwarded);
        $this->assertEquals(100.0, $result->newScore);
    }

    /** @test */
    public function it_handles_very_large_point_values()
    {

        $largePoints = 999999999.99;
        $request = new AwardPointsRequest(
            userId: 1,
            points: $largePoints,
            source: 'bonus',
            metadata: []
        );

        $this->leaderboardRepo->expects($this->once())
            ->method('incrementScore')
            ->with(1, $largePoints)
            ->willReturn($largePoints);

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserRank')
            ->willReturn(1);


        $result = $this->service->awardPoints($request);


        $this->assertEquals($largePoints, $result->pointsAwarded);
        $this->assertEquals($largePoints, $result->newScore);
    }

    /** @test */
    public function it_handles_decimal_point_precision()
    {

        $request = new AwardPointsRequest(
            userId: 1,
            points: 10.123456,
            source: 'test',
            metadata: []
        );

        $this->leaderboardRepo->expects($this->once())
            ->method('incrementScore')
            ->with(1, 10.123456)
            ->willReturn(110.123456);

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserRank')
            ->willReturn(3);


        $result = $this->service->awardPoints($request);


        $this->assertEquals(10.123456, $result->pointsAwarded);
    }

    /** @test */
    public function it_handles_metadata_correctly()
    {

        $metadata = [
            'campaign' => 'summer_sale',
            'transaction_id' => 'TXN123',
        ];

        $request = new AwardPointsRequest(
            userId: 1,
            points: 25.0,
            source: 'referral',
            metadata: $metadata
        );

        $this->leaderboardRepo->method('incrementScore')->willReturn(125.0);
        $this->leaderboardRepo->method('getUserRank')->willReturn(7);


        $result = $this->service->awardPoints($request);


        $this->assertEquals('referral', $result->source);
        $this->assertEquals(25.0, $result->pointsAwarded);
    }

    /** @test */
    public function it_gets_user_rank_for_existing_user()
    {

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserRank')
            ->with(5)
            ->willReturn(10);


        $rank = $this->service->getUserRank(5);


        $this->assertEquals(10, $rank);
    }

    /** @test */
    public function it_returns_null_for_non_existent_user_rank()
    {

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserRank')
            ->with(999)
            ->willReturn(null);


        $rank = $this->service->getUserRank(999);


        $this->assertNull($rank);
    }

    /** @test */
    public function it_gets_user_score_for_existing_user()
    {

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserScore')
            ->with(3)
            ->willReturn(250.5);


        $score = $this->service->getUserScore(3);


        $this->assertEquals(250.5, $score);
    }

    /** @test */
    public function it_returns_null_for_non_existent_user_score()
    {

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserScore')
            ->with(999)
            ->willReturn(null);


        $score = $this->service->getUserScore(999);


        $this->assertNull($score);
    }

    /** @test */
    public function it_returns_null_user_when_user_not_found()
    {

        $this->userRepo->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);


        $user = $this->service->getUserById(999);


        $this->assertTrue($user->isNull());
        $this->assertFalse($user->exists());
    }

    /** @test */
    public function it_returns_actual_user_when_found()
    {

        $mockUser = $this->createMock(User::class);
        $mockUser->method('isNull')->willReturn(false);

        $this->userRepo->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($mockUser);


        $user = $this->service->getUserById(1);


        $this->assertFalse($user->isNull());
    }

    /** @test */
    public function it_handles_first_time_user_award()
    {

        $request = new AwardPointsRequest(
            userId: 100,
            points: 10.0,
            source: 'welcome_bonus',
            metadata: []
        );

        $this->leaderboardRepo->expects($this->once())
            ->method('incrementScore')
            ->with(100, 10.0)
            ->willReturn(10.0);

        $this->leaderboardRepo->expects($this->once())
            ->method('getUserRank')
            ->with(100)
            ->willReturn(1);


        $result = $this->service->awardPoints($request);


        $this->assertEquals(10.0, $result->newScore);
        $this->assertEquals(1, $result->rank);
    }

    /** @test */
    public function it_sets_transaction_id_to_zero_in_response()
    {
        $request = new AwardPointsRequest(
            userId: 1,
            points: 50.0,
            source: 'test',
            metadata: []
        );

        $this->leaderboardRepo->method('incrementScore')->willReturn(50.0);
        $this->leaderboardRepo->method('getUserRank')->willReturn(1);


        $result = $this->service->awardPoints($request);

        $this->assertEquals(0, $result->transactionId);
    }
}
