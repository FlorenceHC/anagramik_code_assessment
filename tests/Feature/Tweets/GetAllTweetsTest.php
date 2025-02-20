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
                    [
                        'id' => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                        'createdAt' => '2017-12-01T11:12:42',
                        'text' => 'Test tweet',
                        'user' => [
                            'id' => '75343078-b5dd-306f-a3f9-8203a3915144',
                            'userName' => 'joe_smith'
                        ]
                    ],
                    [
                        'id' => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c963',
                        'createdAt' => '2017-12-01T11:12:42',
                        'text' => 'Test tweet 2',
                        'user' => [
                            'id' => '75343078-b5dd-306f-a3f9-8203a3915144',
                            'userName' => 'joe_smith2'
                        ]
                    ]
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 10,
                    'total_items' => 2,
                    'total_pages' => 1,
                    'has_more' => false
                ],
            ]);

        // Act
        $response = $this->getJson(route('tweets.index', ['username' => $username, 'page' => $page, 'per_page' => $perPage]));

        // Assert
        $response->assertOk()
            ->assertJson([
                'tweets' => [
                    [
                        'id' => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                        'createdAt' => '2017-12-01T11:12:42',
                        'text' => 'Test tweet',
                        'user' => [
                            'id' => '75343078-b5dd-306f-a3f9-8203a3915144',
                            'userName' => 'joe_smith'
                        ]
                    ],
                    [
                        'id' => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c963',
                        'createdAt' => '2017-12-01T11:12:42',
                        'text' => 'Test tweet 2',
                        'user' => [
                            'id' => '75343078-b5dd-306f-a3f9-8203a3915144',
                            'userName' => 'joe_smith2'
                        ]
                    ]
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 10,
                    'total_items' => 2,
                    'total_pages' => 1,
                    'has_more' => false
                ]
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
