<?php

namespace Tests\Unit\Transformers;

use App\Transformers\TweetTransformer;
use Tests\TestCase;

class TweetTransformerTest extends TestCase
{
    public function test_tweet_with_valid_data_should_return_true()
    {
        // Arrange
        $tweet = [
            'id'        => 1,
            'text'      => 'Some text',
            'createdAt' => '2021-01-01 00:00:00',
            'user'      => ['userName' => 'user'],
        ];

        // Act
        $transformer = new TweetTransformer();

        // Assert
        $this->assertTrue($transformer->isValid($tweet));
    }

    public function test_tweet_without_id_parameter_should_return_false()
    {
        // Arrange
        $tweet = [
            'text'      => 'Some text',
            'createdAt' => '2021-01-01 00:00:00',
            'user'      => ['userName' => 'user'],
        ];

        // Act
        $transformer = new TweetTransformer();

        // Assert
        $this->assertFalse($transformer->isValid($tweet));
    }

    public function test_tweet_without_text_parameter_should_return_false()
    {
        // Arrange
        $tweet = [
            'id'        => 1,
            'createdAt' => '2021-01-01 00:00:00',
            'user'      => ['userName' => 'user'],
        ];

        // Act
        $transformer = new TweetTransformer();

        //
        $this->assertFalse($transformer->isValid($tweet));
    }

    public function test_tweet_without_created_at_parameter_should_return_false()
    {
        // Arrange
        $tweet = [
            'id'   => 1,
            'text' => 'Some text',
            'user' => ['userName' => 'user'],
        ];

        // Act
        $transformer = new TweetTransformer();

        // Assert
        $this->assertFalse($transformer->isValid($tweet));
    }

    public function test_tweet_without_user_user_name_parameter_should_return_false()
    {
        // Arrange
        $tweet = [
            'id'        => 1,
            'text'      => 'Some text',
            'createdAt' => '2021-01-01 00:00:00',
        ];

        // Act
        $transformer = new TweetTransformer();

        // Assert
        $this->assertFalse($transformer->isValid($tweet));
    }

    public function test_tweet_with_empty_user_parameter_should_return_false()
    {
        // Arrange
        $tweet = [
            'id'        => 1,
            'text'      => 'Some text',
            'createdAt' => '2021-01-01 00:00:00',
            'user'      => [],
        ];

        // Act
        $transformer = new TweetTransformer();

        // Assert
        $this->assertFalse($transformer->isValid($tweet));
    }

    public function test_tweet_with_invalid_data_should_send_a_slack_notification()
    {
        // Arrange
        $tweet = [
            'id'        => 1,
            'text'      => 'Some text',
            'createdAt' => 'invalid date',
            'user'      => ['userName' => 'user'],
        ];

        // Act
        $transformer = new TweetTransformer();

        // Assert
        $this->assertFalse($transformer->isValid($tweet));
    }

    public function test_transformer_transforms_data_correctly()
    {
        // Arrange
        $data = [[
            'id'        => 1,
            'text'      => 'Some text',
            'createdAt' => '2021-01-01 00:00:00',
            'user'      => ['userName' => 'user'],
        ]];

        // Act
        $transformer = new TweetTransformer();

        // Assert
        $this->assertEquals([[
            'id'        => 1,
            'text'      => 'Some text',
            'createdAt' => '2021-01-01 00:00:00',
            'user'      => ['userName' => 'user'],
        ]], $transformer->transform($data));
    }
}
