<?php

namespace Tests\Unit\Services;

use App\Services\TweetService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TweetServiceTest extends TestCase
{
    public function test_it_should_trigger_an_http_request_with_proper_parameters_and_return_correct_response()
    {
        // Arrange
        $userName = 'joe_smith';
        $mockTweets = [
            [
                'id' => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text' => 'Test tweet',
                'user' => [
                    'id' => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith'
                ]
            ]
        ];

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response($mockTweets, 200)
        ]);

        $tweetService = new TweetService();
        $result = $tweetService->getTweets($userName);

        // Assert
        Http::assertSent(function (Request $request) use ($userName, $tweetService) {
            return $request->url() === "https://app.codescreen.com/api/assessments/tweets?userName=joe_smith" &&
                $request->header('Authorization')[0] === 'Bearer 8c5996d5-fb89-46c9-8821-7063cfbc18b1' &&
                $request['userName'] === $userName;
        });

        $this->assertEquals([
            'tweets' => $mockTweets,
            'pagination' => [
                'current_page' => 1,
                'per_page' => 10,
                'total_items' => 1,
                'total_pages' => 1,
                'has_more' => false
            ]
        ], $result);
    }

    public function test_in_case_of_consecutive_calls_cached_data_should_be_returned_and_only_one_http_request_should_be_triggered()
    {
        // Arrange
        $userName = 'joe_smith';

        $mockTweets = [
            [
                'id' => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text' => 'Test tweet',
                'user' => [
                    'id' => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith'
                ]
            ]
        ];

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response($mockTweets, 200)
        ]);

        $tweetService = new TweetService();
        $tweetService->getTweets($userName);
        $tweetService->getTweets($userName);

        // Assert
        Http::assertSentCount(1);
    }

    public function test_if_http_request_fails_it_should_throw_an_exception()
    {
        // Arrange
        $userName = 'joe_smith';

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response(new \Exception('request exception'), 500),
        ]);

        $tweetService = new TweetService();
        try {
            $tweetService->getTweets($userName);
        } catch (\Throwable $throwable) {
            // Assert
            $this->assertEquals('Failed to fetch tweets: 500', $throwable->getMessage());
            return;
        }

        $this->fail('Exception has not been thrown');
    }

    public function test_it_should_return_paginated_data_if_there_are_too_many_tweets()
    {
        // Arrange
        $userName = 'joe_smith';
        $mockTweets = [
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
                'id' => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text' => 'Test tweet 2',
                'user' => [
                    'id' => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith2'
                ]
            ],
            [
                'id' => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text' => 'Test tweet 3',
                'user' => [
                    'id' => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith'
                ]
            ]
        ];

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response($mockTweets, 200)
        ]);

        $tweetService = new TweetService();
        $result = $tweetService->getTweets($userName, 2, 1);

        // Assert
        Http::assertSent(function (Request $request) use ($userName, $tweetService) {
            return $request->url() === "https://app.codescreen.com/api/assessments/tweets?userName=joe_smith" &&
                $request->header('Authorization')[0] === 'Bearer 8c5996d5-fb89-46c9-8821-7063cfbc18b1' &&
                $request['userName'] === $userName;
        });

        $this->assertEquals([
            'tweets' => [
                $mockTweets[1] // only one tweet
            ],
            'pagination' => [
                'current_page' => 2, // we are on the second page
                'per_page' => 1, // we want only 1 tweet
                'total_items' => 3, // we have 3 tweets
                'total_pages' => 3, // we have 3 tweets
                'has_more' => true
            ]
        ], $result);
    }
}
