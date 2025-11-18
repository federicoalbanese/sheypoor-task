<?php

namespace Tests\Unit\Repositories;

use App\Repositories\RedisLeaderboardRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class RedisLeaderboardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RedisLeaderboardRepository $repository;
    private string $testKey = 'test_leaderboard';

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RedisLeaderboardRepository($this->testKey);

        Redis::del($this->testKey);
    }

    protected function tearDown(): void
    {
        Redis::del($this->testKey);
        parent::tearDown();
    }

    /** @test */
    public function it_adds_score_for_new_user()
    {
        $result = $this->repository->addScore(1, 100.0);

        $this->assertTrue($result);
        $this->assertEquals(100.0, Redis::zscore($this->testKey, 1));
    }

    /** @test */
    public function it_replaces_score_when_adding_for_existing_user()
    {
        Redis::zadd($this->testKey, 50.0, 1);

        $this->repository->addScore(1, 100.0);

        $this->assertEquals(100.0, Redis::zscore($this->testKey, 1));
    }

    /** @test */
    public function it_increments_score_atomically()
    {
        Redis::zadd($this->testKey, 50.0, 1);

        $newScore = $this->repository->incrementScore(1, 25.0);

        $this->assertEquals(75.0, $newScore);
        $this->assertEquals(75.0, Redis::zscore($this->testKey, 1));
    }

    /** @test */
    public function it_increments_score_for_new_user_starting_from_zero()
    {
        $newScore = $this->repository->incrementScore(999, 10.0);

        $this->assertEquals(10.0, $newScore);
    }

    /** @test */
    public function it_handles_negative_increment()
    {
        Redis::zadd($this->testKey, 100.0, 1);

        $newScore = $this->repository->incrementScore(1, -30.0);

        $this->assertEquals(70.0, $newScore);
    }

    /** @test */
    public function it_handles_zero_increment()
    {
        Redis::zadd($this->testKey, 100.0, 1);

        $newScore = $this->repository->incrementScore(1, 0.0);

        $this->assertEquals(100.0, $newScore);
    }

    /** @test */
    public function it_gets_correct_rank_for_user()
    {
        Redis::zadd($this->testKey, 100.0, 1);
        Redis::zadd($this->testKey, 200.0, 2);
        Redis::zadd($this->testKey, 150.0, 3);

        $this->assertEquals(1, $this->repository->getUserRank(2));
        $this->assertEquals(2, $this->repository->getUserRank(3));
        $this->assertEquals(3, $this->repository->getUserRank(1));
    }

    /** @test */
    public function it_returns_null_rank_for_non_existent_user()
    {
        $rank = $this->repository->getUserRank(999);

        $this->assertNull($rank);
    }

    /** @test */
    public function it_handles_tied_scores_consistently()
    {
        Redis::zadd($this->testKey, 100.0, 1);
        Redis::zadd($this->testKey, 100.0, 2);
        Redis::zadd($this->testKey, 100.0, 3);

        $rank1 = $this->repository->getUserRank(1);
        $rank2 = $this->repository->getUserRank(2);
        $rank3 = $this->repository->getUserRank(3);

        $this->assertNotNull($rank1);
        $this->assertNotNull($rank2);
        $this->assertNotNull($rank3);
    }

    /** @test */
    public function it_gets_top_users_in_descending_order()
    {
        Redis::zadd($this->testKey, 100.0, 1);
        Redis::zadd($this->testKey, 300.0, 2);
        Redis::zadd($this->testKey, 200.0, 3);

        $topUsers = $this->repository->getTopUsers(3);

        $this->assertCount(3, $topUsers);
        $this->assertEquals(2, $topUsers[0]['user_id']);
        $this->assertEquals(300.0, $topUsers[0]['score']);
        $this->assertEquals(1, $topUsers[0]['rank']);

        $this->assertEquals(3, $topUsers[1]['user_id']);
        $this->assertEquals(200.0, $topUsers[1]['score']);
        $this->assertEquals(2, $topUsers[1]['rank']);
    }

    /** @test */
    public function it_limits_top_users_correctly()
    {
        for ($i = 1; $i <= 100; $i++) {
            Redis::zadd($this->testKey, $i * 10, $i);
        }

        $topUsers = $this->repository->getTopUsers(10);

        $this->assertCount(10, $topUsers);
        $this->assertEquals(1000.0, $topUsers[0]['score']);
    }

    /** @test */
    public function it_returns_empty_array_for_empty_leaderboard()
    {
        $topUsers = $this->repository->getTopUsers(10);

        $this->assertEmpty($topUsers);
    }

    /** @test */
    public function it_returns_fewer_users_when_total_is_less_than_limit()
    {
        Redis::zadd($this->testKey, 100.0, 1);
        Redis::zadd($this->testKey, 200.0, 2);

        $topUsers = $this->repository->getTopUsers(10);

        $this->assertCount(2, $topUsers);
    }

    /** @test */
    public function it_gets_user_score()
    {
        Redis::zadd($this->testKey, 123.45, 1);

        $score = $this->repository->getUserScore(1);

        $this->assertEquals(123.45, $score);
    }

    /** @test */
    public function it_returns_null_for_non_existent_user_score()
    {
        $score = $this->repository->getUserScore(999);

        $this->assertNull($score);
    }

    /** @test */
    public function it_handles_very_large_scores()
    {
        $largeScore = 99999999999.99;
        Redis::zadd($this->testKey, $largeScore, 1);

        $score = $this->repository->getUserScore(1);

        $this->assertEquals($largeScore, $score);
    }

    /** @test */
    public function it_handles_decimal_precision_correctly()
    {
        $preciseScore = 123.456789;
        Redis::zadd($this->testKey, $preciseScore, 1);

        $score = $this->repository->getUserScore(1);

        $this->assertEqualsWithDelta($preciseScore, $score, 0.000001);
    }

    /** @test */
    public function it_maintains_correct_ranks_after_multiple_increments()
    {
        Redis::zadd($this->testKey, 100.0, 1);
        Redis::zadd($this->testKey, 100.0, 2);
        Redis::zadd($this->testKey, 100.0, 3);

        $this->repository->incrementScore(2, 50.0);

        $this->assertEquals(1, $this->repository->getUserRank(2));
        // For tied scores (100.0), Redis sorts by member value in reverse for ZREVRANGE
        // So user 3 (member "3") ranks higher than user 1 (member "1")
        $this->assertEquals(2, $this->repository->getUserRank(3));
        $this->assertEquals(3, $this->repository->getUserRank(1));
    }

    /** @test */
    public function it_formats_leaderboard_with_correct_structure()
    {
        Redis::zadd($this->testKey, 100.0, 1);

        $topUsers = $this->repository->getTopUsers(1);

        $this->assertArrayHasKey('rank', $topUsers[0]);
        $this->assertArrayHasKey('user_id', $topUsers[0]);
        $this->assertArrayHasKey('score', $topUsers[0]);
        $this->assertIsInt($topUsers[0]['rank']);
        $this->assertIsInt($topUsers[0]['user_id']);
        $this->assertIsFloat($topUsers[0]['score']);
    }

    /** @test */
    public function it_handles_user_id_zero()
    {
        Redis::zadd($this->testKey, 100.0, 0);

        $score = $this->repository->getUserScore(0);
        $rank = $this->repository->getUserRank(0);

        $this->assertEquals(100.0, $score);
        $this->assertEquals(1, $rank);
    }

    /** @test */
    public function it_handles_single_user_leaderboard()
    {
        Redis::zadd($this->testKey, 100.0, 1);

        $topUsers = $this->repository->getTopUsers(10);

        $this->assertCount(1, $topUsers);
        $this->assertEquals(1, $topUsers[0]['rank']);
        $this->assertEquals(1, $topUsers[0]['user_id']);
    }
}
