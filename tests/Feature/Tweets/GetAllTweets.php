<?php

namespace Tests\Feature\Tweets;

use App\Services\TweetService;
use Tests\TestCase;

class GetAllTweets extends TestCase
{
    public function test_it_should_return_tweets()
    {
        // Arrange
        $username = 'joe_smith';

        $tweetServiceMock = $this->mock(TweetService::class);
        $tweetServiceMock->shouldReceive('getTweets')
            ->with($username)
            ->once()
            ->andReturn([
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
            ]);

        // Act
        $response = $this->getJson(route('tweets.index', ['username' => $username]));

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'createdAt',
                    'text',
                    'user' => [
                        'id',
                        'userName'
                    ]
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
