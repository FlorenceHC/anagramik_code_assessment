<?php

namespace Tests\Feature\Tweets;

use App\Services\TweetService;
use Tests\TestCase;

class GetAllTweetsTest extends TestCase
{
    public function test_it_should_return_tweets()
    {
        // Arrange
        $username = 'joe_smith';
        $page = 1;
        $perPage = 10;

        $tweetServiceMock = $this->mock(TweetService::class);
        $tweetServiceMock->shouldReceive('getTweets')
            ->with($username, $page, $perPage)
            ->once()
            ->andReturn([
                'tweets' => [
                    'fake-tweets-data'
                ],
                'pagination' => [
                    'fake-pagination-data'
                ],
                'all_tweets' => [
                    'fake-all_tweets-data'
                ]
            ]);

        $tweetServiceMock->shouldReceive('getAnalytics')
            ->with(['fake-all_tweets-data'])
            ->once()
            ->andReturn([
                'fake-analytics-data'
            ]);

        // Act
        $response = $this->getJson(route('tweets.index', ['username' => $username, 'page' => $page, 'per_page' => $perPage]));

        // Assert
        $response->assertOk()
            ->assertExactJson([
                'tweets' => [
                    'fake-tweets-data'
                ],
                'pagination' => [
                    'fake-pagination-data'
                ],
                'all_tweets' => [
                    'fake-all_tweets-data'
                ],
                'fake-analytics-data'
            ]);
    }

    public function test_username_is_required()
    {
        // Act
        $response = $this->getJson(route('tweets.index'));

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }
}
