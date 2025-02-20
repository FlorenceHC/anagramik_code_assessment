<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TweetService
{
    // TODO: move consts to some config file for data easier manipulation
    private const API_URL = 'https://app.codescreen.com/api/assessments/tweets';

    private const API_TOKEN = '8c5996d5-fb89-46c9-8821-7063cfbc18b1';

    private const CACHE_TTL = 1800; // 30 minutes in seconds

    public function getTweetsFromApi(string $userName): array
    {
        $response = Http::withToken(self::API_TOKEN)
            ->get(self::API_URL, [
                'userName' => $userName,
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch tweets: '.$response->status());
        }

        return $response->json();
    }

    public function getTweets(string $userName, ?int $page = 1, ?int $perPage = 10): array
    {
        $cacheKey = "tweets.{$userName}";

        $tweets = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userName) {
            return $this->getTweetsFromApi($userName);
        });

        $offset      = ($page - 1) * $perPage;
        $totalTweets = count($tweets);
        $totalPages  = ceil($totalTweets / $perPage);

        return [
            'tweets'     => array_slice($tweets, $offset, $perPage),
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_items'  => $totalTweets,
                'total_pages'  => $totalPages,
                'has_more'     => $page < $totalPages,
            ],
            'all_tweets' => $tweets,
        ];
    }

    public function getAnalytics(array $tweets): array
    {
        if (empty($tweets)) {
            return [];
        }

        // TODO: I am constantly iterating over the same array - find a better solution
        $numberOfTweetsPerDay = $this->getNumberOfTweetsPerDay($tweets);

        return [
            'totalTweets'              => count($tweets),
            'longestTweetId'           => $this->getLongestTweetById($tweets)['id'],
            'maxDaysBetweenTweets'     => $this->getMostDaysBetweenTweets($tweets),
            'mostPopularHashtag'       => $this->getMostPopularHashtag($tweets),
            'mostNumberOfTweetsPerDay' => max($numberOfTweetsPerDay),
            'numberOfTweetsPerDay'     => $numberOfTweetsPerDay,
        ];
    }

    public function getLongestTweetById(array $tweets): array
    {
        $longestTweet       = null;
        $longestTweetLength = 0;

        foreach ($tweets as $tweet) {
            $tweetLength = strlen($tweet['text']);
            if ($tweetLength > $longestTweetLength) {
                $longestTweetLength = $tweetLength;
                $longestTweet       = $tweet;
            }
        }

        return $longestTweet;
    }

    public function getMostDaysBetweenTweets(array $tweets): int
    {
        $sortedTweets = collect($tweets)->sortBy('createdAt')->values();

        $maxDays = 0;

        for ($i = 0; $i < count($sortedTweets) - 1; $i++) {
            $current    = Carbon::parse($sortedTweets[$i]['createdAt']);
            $future     = Carbon::parse($sortedTweets[$i + 1]['createdAt']);
            $diffInDays = $current->diffInDays($future);
            $maxDays    = max($maxDays, $diffInDays);
        }

        return $maxDays;
    }

    public function getMostPopularHashtag(array $tweets): string
    {
        $hashtags = [];
        foreach ($tweets as $tweet) {
            // find all hashtags in tweet
            preg_match_all('/#\w+/', $tweet['text'], $matches);

            // loop over all of them and update global count
            foreach ($matches[0] as $hashtag) {
                if (! isset($hashtags[$hashtag])) {
                    $hashtags[$hashtag] = 0;
                }
                $hashtags[$hashtag] += 1;
            }
        }

        // this will find the biggest value in the array and return the key
        return array_search(max($hashtags), $hashtags);
    }

    public function getNumberOfTweetsPerDay(array $tweets): array
    {
        $tweetsPerDay = [];
        foreach ($tweets as $tweet) {
            $date                = Carbon::parse($tweet['createdAt'])->toDateString();
            $tweetsPerDay[$date] = ($tweetsPerDay[$date] ?? 0) + 1;
        }

        return $tweetsPerDay;
    }
}
