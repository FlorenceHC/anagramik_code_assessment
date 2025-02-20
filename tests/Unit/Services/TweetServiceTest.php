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

        $this->assertEquals($mockTweets, $result);
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
}
