# Leaderboard System

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Background Jobs

Laravel 12 has this neat feature called deferred jobs that run after the HTTP response is sent. I'm using that to save everything to the database without blocking the API response.

If the job fails, it retries 5 times with exponential backoff. If it still fails, it goes to a dead letter queue so we can investigate later.

## Architecture Decisions

### 1. Redis as Source of Truth

**Why:** Needed fast reads and writes. Redis sorted sets are perfect for leaderboards - O(log N) for most operations.

**Trade-off:** If Redis crashes, we lose recent data. Mitigated by:
- Redis persistence (AOF + RDB)
- Background jobs sync to database
- Can rebuild Redis from database if needed

### 2. Event Sourcing Pattern

The `point_transactions` table is append-only. Every point award is recorded. This means:
- Full audit trail
- Can rebuild state from events
- Easy to debug issues

The `leaderboard_scores` table is just a materialized view for performance.

### 3. Async Database Writes

Award endpoint only touches Redis, then dispatches a job. This made response times go from ~15ms to ~2ms.

The job handles:
- Creating transaction record
- Updating materialized view
- Error handling

### 4. DTOs Instead of Arrays

Using typed DTOs everywhere instead of passing arrays around. Makes the code easier to understand and catches bugs early.

### 5. Repository Pattern

Separated data access from business logic. Makes it easier to:
- Swap Redis for something else later
- Write tests
- Keep code organized

## API Endpoints

### Award Points
```bash
POST /api/leaderboard/award
{
  "user_id": 1,
  "points": 50,
  "source": "purchase"
}
```

### Get Leaderboard
```bash
GET /api/leaderboard?limit=100
```

### Get User Rank
```bash
GET /api/leaderboard/user/1
```

Response:
```json
{
  "user_id": 1,
  "username": "john",
  "rank": 5,
  "score": 1250.0
}
```
