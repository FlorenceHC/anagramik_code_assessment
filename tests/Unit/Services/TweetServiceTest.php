<?php

namespace Tests\Unit\Services;

use App\Services\TweetService;
use App\Transformers\TweetTransformer;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TweetServiceTest extends TestCase
{
    public function test_it_should_trigger_an_http_request_with_proper_parameters_and_return_correct_response()
    {
        // Arrange
        $userName   = 'joe_smith';
        $mockTweets = [
            [
                'id'        => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text'      => 'Test tweet',
                'user'      => [
                    'id'       => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith',
                ],
            ],
        ];
        $tweetTransformerMock = $this->mock(TweetTransformer::class);
        $tweetTransformerMock->shouldReceive('transform')->once()->with($mockTweets)->andReturn($mockTweets);

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response($mockTweets, 200),
        ]);

        $tweetService = new TweetService($tweetTransformerMock);
        $result       = $tweetService->getTweets($userName);

        // Assert
        Http::assertSent(function (Request $request) use ($userName) {
            return $request->url() === 'https://app.codescreen.com/api/assessments/tweets?userName=joe_smith' && $request->header('Authorization')[0] === 'Bearer 8c5996d5-fb89-46c9-8821-7063cfbc18b1' && $request['userName'] === $userName;
        });

        $this->assertEquals([
            'tweets'     => $mockTweets,
            'pagination' => [
                'current_page' => 1,
                'per_page'     => 10,
                'total_items'  => 1,
                'total_pages'  => 1,
                'has_more'     => false,
            ],
            'all_tweets' => $mockTweets,
        ], $result);
    }

    public function test_in_case_of_consecutive_calls_cached_data_should_be_returned_and_only_one_http_request_should_be_triggered()
    {
        // Arrange
        $userName = 'joe_smith';

        $mockTweets = [
            [
                'id'        => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text'      => 'Test tweet',
                'user'      => [
                    'id'       => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith',
                ],
            ],
        ];
        $tweetTransformerMock = $this->mock(TweetTransformer::class);
        $tweetTransformerMock->shouldReceive('transform')->once()->with($mockTweets)->andReturn($mockTweets);

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response($mockTweets, 200),
        ]);

        $tweetService = new TweetService($tweetTransformerMock);
        $tweetService->getTweets($userName);
        $tweetService->getTweets($userName);

        // Assert
        Http::assertSentCount(1);
    }

    public function test_it_should_return_paginated_data_if_there_are_too_many_tweets()
    {
        // Arrange
        $userName   = 'joe_smith';
        $mockTweets = [
            [
                'id'        => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text'      => 'Test tweet',
                'user'      => [
                    'id'       => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith',
                ],
            ],
            [
                'id'        => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text'      => 'Test tweet 2',
                'user'      => [
                    'id'       => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith2',
                ],
            ],
            [
                'id'        => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-12-01T11:12:42',
                'text'      => 'Test tweet 3',
                'user'      => [
                    'id'       => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith',
                ],
            ],
        ];
        $tweetTransformerMock = $this->mock(TweetTransformer::class);
        $tweetTransformerMock->shouldReceive('transform')->once()->with($mockTweets)->andReturn($mockTweets);

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response($mockTweets, 200),
        ]);

        $tweetService = new TweetService($tweetTransformerMock);
        $result       = $tweetService->getTweets($userName, 2, 1);

        // Assert
        Http::assertSent(function (Request $request) use ($userName) {
            return $request->url() === 'https://app.codescreen.com/api/assessments/tweets?userName=joe_smith' && $request->header('Authorization')[0] === 'Bearer 8c5996d5-fb89-46c9-8821-7063cfbc18b1' && $request['userName'] === $userName;
        });

        $this->assertEquals([
            'tweets' => [
                $mockTweets[1], // only one tweet
            ],
            'pagination' => [
                'current_page' => 2, // we are on the second page
                'per_page'     => 1, // we want only 1 tweet
                'total_items'  => 3, // we have 3 tweets
                'total_pages'  => 3, // we have 3 tweets
                'has_more'     => true,
            ],
            'all_tweets' => $mockTweets,
        ], $result);
    }

    public function test_it_should_return_longest_tweet_by_id()
    {
        // Arrange
        $dummy_tweets_data = storage_path('app/dummy_data.json');
        $tweets            = json_decode(file_get_contents($dummy_tweets_data), true);

        $tweetService = new TweetService(new TweetTransformer());

        // Act
        $longest_tweet = $tweetService->getLongestTweetById($tweets);

        // Assert
        $this->assertEquals('0c2dc961-a0ae-470e-81a6-8320504dae14', $longest_tweet['id']);

    }

    public function test_it_should_return_most_days_between_tweets()
    {
        // Arrange
        $dummy_tweets_data = storage_path('app/dummy_data.json');
        $tweets            = json_decode(file_get_contents($dummy_tweets_data), true);

        $tweetService = new TweetService(new TweetTransformer());

        // Act
        $most_days_between_tweets = $tweetService->getMostDaysBetweenTweets($tweets);

        // Assert
        $this->assertEquals(120, $most_days_between_tweets);
    }

    public function test_it_should_return_most_popular_hashtag()
    {
        // Arrange
        $dummy_tweets_data = storage_path('app/dummy_data.json');
        $tweets            = json_decode(file_get_contents($dummy_tweets_data), true);

        $tweetService = new TweetService(new TweetTransformer());

        // Act
        $most_popular_hashtag = $tweetService->getMostPopularHashtag($tweets);

        // Assert
        $this->assertEquals('#WorldCup2018', $most_popular_hashtag);
    }

    public function test_it_should_return_most_number_of_tweets_per_day()
    {
        // Arrange
        $dummy_tweets_data = storage_path('app/dummy_data.json');
        $tweets            = json_decode(file_get_contents($dummy_tweets_data), true);

        $tweetService = new TweetService(new TweetTransformer());

        // Act
        $most_number_of_tweets_per_day = $tweetService->getNumberOfTweetsPerDay($tweets);

        // Assert
        $this->assertEquals(10, max($most_number_of_tweets_per_day));
    }

    public function test_get_analytics_method_should_call_proper_internal_methods()
    {
        // Arrange
        $dummy_tweets_data = storage_path('app/dummy_data.json');
        $tweets            = json_decode(file_get_contents($dummy_tweets_data), true);

        $tweetServiceMock = $this->partialMock(TweetService::class);

        $tweetServiceMock->shouldReceive('getLongestTweetById')->once()->with($tweets)->andReturn(['id' => 'fake-id']);
        $tweetServiceMock->shouldReceive('getMostDaysBetweenTweets')->once()->with($tweets)->andReturn(0);
        $tweetServiceMock->shouldReceive('getMostPopularHashtag')->once()->with($tweets)->andReturn('');
        $tweetServiceMock->shouldReceive('getNumberOfTweetsPerDay')->once()->with($tweets)->andReturn(['fake-data']);

        // Act
        $result = $tweetServiceMock->getAnalytics($tweets);

        // Assert
        $this->assertEquals([
            'totalTweets'              => 39,
            'longestTweetId'           => 'fake-id',
            'maxDaysBetweenTweets'     => 0,
            'mostPopularHashtag'       => '',
            'mostNumberOfTweetsPerDay' => 'fake-data',
            'numberOfTweetsPerDay'     => ['fake-data'],
        ], $result);
    }

    public function test_it_should_return_tweets_in_chronological_order()
    {
        // Arrange
        $userName   = 'joe_smith';
        $mockTweets = [
            [
                'id'        => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-02-02T11:12:42',
                'text'      => 'Test tweet 2',
                'user'      => [
                    'id'       => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith',
                ],
            ],
            [
                'id'        => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-01-01T11:12:42',
                'text'      => 'Test tweet 1',
                'user'      => [
                    'id'       => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith',
                ],
            ],
            [
                'id'        => '52f83d7c-ad2c-4ca0-b742-b03bc27f0c96',
                'createdAt' => '2017-03-03T11:12:42',
                'text'      => 'Test tweet 3',
                'user'      => [
                    'id'       => '75343078-b5dd-306f-a3f9-8203a3915144',
                    'userName' => 'joe_smith',
                ],
            ],
        ];
        $tweetTransformerMock = $this->mock(TweetTransformer::class);
        $tweetTransformerMock->shouldReceive('transform')->once()->with($mockTweets)->andReturn($mockTweets);

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response($mockTweets, 200),
        ]);

        $tweetService = new TweetService($tweetTransformerMock);
        $result       = $tweetService->getTweets($userName, 1, 3);

        // Assert
        $this->assertEquals([
            'tweets' => [
                $mockTweets[1], // oldest tweet
                $mockTweets[0], // middle tweet
                $mockTweets[2], // newest tweet
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page'     => 3,
                'total_items'  => 3,
                'total_pages'  => 1,
                'has_more'     => false,
            ],
            'all_tweets' => [
                $mockTweets[1], // oldest tweet
                $mockTweets[0], // middle tweet
                $mockTweets[2], // newest tweet
            ],
        ], $result);
    }

    public function test_it_should_return_an_empty_array_if_there_is_no_response()
    {
        // Arrange
        $userName   = 'joe_smith';
        $mockTweets = [];

        // Act
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response($mockTweets, 200),
        ]);

        $tweetService = new TweetService(new TweetTransformer());
        $result       = $tweetService->getTweets($userName, 1, 3);

        // Assert
        $this->assertEquals([
            'tweets' => [
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page'     => 3,
                'total_items'  => 0,
                'total_pages'  => 0,
                'has_more'     => false,
            ],
            'all_tweets' => [
            ],
        ], $result);
    }

    #[DataProvider('http_error_provider')]
    public function test_get_tweets_from_api_handles_http_errors(int $status, string $error, string $exception): void
    {
        // Arrange
        $userName = 'joe_smith';
        Http::fake([
            'app.codescreen.com/api/assessments/tweets*' => Http::response([
                'message' => $error,
            ], $status),
        ]);

        $tweetService = new TweetService(new TweetTransformer());

        try {
            // Act
            $tweetService->getTweetsFromApi($userName);
        } catch (RequestException $e) {
            // Assert
            $message = json_decode($e->response->getBody()->getContents())->message;
            $this->assertEquals($error, $message);
            $this->assertInstanceOf($exception, $e);

            return;
        }

        $this->fail('Exception has not been thrown');
    }

    public static function http_error_provider(): array
    {
        return [
            'bad request' => [
                'status'    => 400,
                'error'     => 'Bad Request',
                'exception' => RequestException::class,
            ],
            'unauthorized' => [
                'status'    => 401,
                'error'     => 'Unauthorized',
                'exception' => RequestException::class,
            ],
            'forbidden' => [
                'status'    => 403,
                'error'     => 'Forbidden',
                'exception' => RequestException::class,
            ],
            'not found' => [
                'status'    => 404,
                'error'     => 'Not Found',
                'exception' => RequestException::class,
            ],
            'method not allowed' => [
                'status'    => 405,
                'error'     => 'Method Not Allowed',
                'exception' => RequestException::class,
            ],
            'server error' => [
                'status'    => 500,
                'error'     => 'Internal Server Error',
                'exception' => RequestException::class,
            ],
            'service unavailable' => [
                'status'    => 503,
                'error'     => 'Service Unavailable',
                'exception' => RequestException::class,
            ],
        ];
    }
}
