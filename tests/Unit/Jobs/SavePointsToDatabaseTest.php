<?php

namespace Tests\Unit\Jobs;

use App\Contracts\Repositories\LeaderboardRepositoryInterface;
use App\Jobs\SavePointsToDatabase;
use App\Models\FailedLeaderboardSync;
use App\Models\LeaderboardScore;
use App\Models\PointTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SavePointsToDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private LeaderboardRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LeaderboardRepositoryInterface::class);

        // Create default test user for most tests
        $this->createTestUser(1);
    }

    private function createTestUser(int $id): void
    {
        if (!\App\Models\User::find($id)) {
            \App\Models\User::create([
                'id' => $id,
                'username' => "user{$id}"
            ]);
        }
    }

    /** @test */
    public function it_creates_point_transaction_and_updates_leaderboard_score()
    {

        $job = new SavePointsToDatabase(
            userId: 1,
            points: 50.0,
            source: 'purchase',
            metadata: ['campaign' => 'test']
        );

        $this->repository->expects($this->once())
            ->method('getUserScore')
            ->with(1)
            ->willReturn(150.0);


        $job->handle($this->repository);


        $this->assertDatabaseHas('leaderboard_point_transactions', [
            'user_id' => 1,
            'points' => 50.0,
            'source' => 'purchase',
        ]);

        $this->assertDatabaseHas('leaderboard_scores', [
            'user_id' => 1,
            'score' => 150.0,
        ]);
    }

    /** @test */
    public function it_updates_existing_leaderboard_score()
    {
        LeaderboardScore::create([
            'user_id' => 1,
            'score' => 100.0,
            'last_transaction_id' => 1,
        ]);

        $job = new SavePointsToDatabase(
            userId: 1,
            points: 50.0,
            source: 'purchase',
            metadata: []
        );

        $this->repository->expects($this->once())
            ->method('getUserScore')
            ->with(1)
            ->willReturn(150.0);

        $job->handle($this->repository);

        $leaderboardScore = LeaderboardScore::where('user_id', 1)->first();
        $this->assertEquals(150.0, $leaderboardScore->score);
    }

    /** @test */
    public function it_stores_transaction_metadata_correctly()
    {
        $metadata = [
            'campaign' => 'summer_sale',
            'referral_code' => 'ABC123',
            'bonus_multiplier' => 2.0,
        ];

        $job = new SavePointsToDatabase(
            userId: 1,
            points: 25.0,
            source: 'referral',
            metadata: $metadata
        );

        $this->repository->method('getUserScore')->willReturn(25.0);

        $job->handle($this->repository);

        $transaction = PointTransaction::where('user_id', 1)->first();
        $this->assertEquals($metadata, $transaction->metadata);
    }

    /** @test */
    public function it_updates_last_transaction_id_correctly()
    {

        $job = new SavePointsToDatabase(
            userId: 1,
            points: 50.0,
            source: 'test',
            metadata: []
        );

        $this->repository->method('getUserScore')->willReturn(50.0);


        $job->handle($this->repository);


        $transaction = PointTransaction::where('user_id', 1)->first();
        $leaderboardScore = LeaderboardScore::where('user_id', 1)->first();

        $this->assertEquals($transaction->id, $leaderboardScore->last_transaction_id);
    }

    /** @test */
    public function it_handles_zero_points()
    {

        $job = new SavePointsToDatabase(
            userId: 1,
            points: 0.0,
            source: 'test',
            metadata: []
        );

        $this->repository->method('getUserScore')->willReturn(100.0);


        $job->handle($this->repository);


        $this->assertDatabaseHas('leaderboard_point_transactions', [
            'user_id' => 1,
            'points' => 0.0,
        ]);
    }

    /** @test */
    public function it_handles_very_large_points()
    {

        $largePoints = 999999999.99;
        $job = new SavePointsToDatabase(
            userId: 1,
            points: $largePoints,
            source: 'jackpot',
            metadata: []
        );

        $this->repository->method('getUserScore')->willReturn($largePoints);


        $job->handle($this->repository);


        $this->assertDatabaseHas('leaderboard_point_transactions', [
            'user_id' => 1,
            'points' => $largePoints,
        ]);
    }

    /** @test */
    public function it_handles_decimal_precision()
    {

        $precisePoints = 12.345678;
        $job = new SavePointsToDatabase(
            userId: 1,
            points: $precisePoints,
            source: 'test',
            metadata: []
        );

        $this->repository->method('getUserScore')->willReturn($precisePoints);


        $job->handle($this->repository);


        // Points are cast to decimal:2, so we expect rounding to 2 decimal places
        $transaction = PointTransaction::where('user_id', 1)->first();
        $this->assertEquals(12.35, (float) $transaction->points);
    }

    /** @test */
    public function it_logs_failure_to_dead_letter_queue()
    {

        Log::shouldReceive('error')
            ->once()
            ->with('Database sync permanently failed', \Mockery::any());

        $job = new SavePointsToDatabase(
            userId: 1,
            points: 50.0,
            source: 'test',
            metadata: []
        );

        $exception = new \Exception('Database connection failed');


        $job->failed($exception);


        $this->assertDatabaseHas('failed_leaderboard_syncs', [
            'user_id' => 1,
            'error' => 'Database connection failed',
        ]);
    }

    /** @test */
    public function it_handles_empty_metadata()
    {

        $job = new SavePointsToDatabase(
            userId: 1,
            points: 10.0,
            source: 'test',
            metadata: []
        );

        $this->repository->method('getUserScore')->willReturn(10.0);


        $job->handle($this->repository);


        $transaction = PointTransaction::where('user_id', 1)->first();
        $this->assertIsArray($transaction->metadata);
        $this->assertEmpty($transaction->metadata);
    }

    /** @test */
    public function it_handles_null_metadata_as_empty_array()
    {
        $job = new SavePointsToDatabase(
            userId: 1,
            points: 10.0,
            source: 'test'
        );

        $this->repository->method('getUserScore')->willReturn(10.0);


        $job->handle($this->repository);


        $transaction = PointTransaction::where('user_id', 1)->first();
        $this->assertIsArray($transaction->metadata);
        $this->assertEmpty($transaction->metadata);
    }

    /** @test */
    public function it_does_not_create_duplicate_leaderboard_scores()
    {

        $job1 = new SavePointsToDatabase(userId: 1, points: 10.0, source: 'test', metadata: []);
        $job2 = new SavePointsToDatabase(userId: 1, points: 20.0, source: 'test', metadata: []);

        $this->repository->method('getUserScore')
            ->willReturnOnConsecutiveCalls(10.0, 30.0);


        $job1->handle($this->repository);
        $job2->handle($this->repository);

        $count = LeaderboardScore::where('user_id', 1)->count();
        $this->assertEquals(1, $count);

        $score = LeaderboardScore::where('user_id', 1)->first();
        $this->assertEquals(30.0, $score->score);
    }

    /** @test */
    public function it_has_correct_retry_configuration()
    {

        $job = new SavePointsToDatabase(
            userId: 1,
            points: 10.0,
            source: 'test',
            metadata: []
        );


        $this->assertEquals(5, $job->tries);
        $this->assertEquals([10, 30, 60, 120], $job->backoff);
        $this->assertEquals(30, $job->timeout);
    }
}
