<?php

namespace App\Providers;

use App\Contracts\Repositories\LeaderboardRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\RedisLeaderboardRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(LeaderboardRepositoryInterface::class, RedisLeaderboardRepository::class);
    }

    public function boot(): void
    {
    }
}
