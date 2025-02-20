<?php

namespace App\Services;

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
                'userName' => $userName
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch tweets: ' . $response->status());
        }

        return $response->json();
    }

    public function getTweets(string $userName, ?int $page = 1, ?int $perPage = 10): array
    {
        $cacheKey = "tweets.{$userName}";

        $tweets = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userName) {
            return $this->getTweetsFromApi($userName);
        });

        $offset = ($page - 1) * $perPage;
        $totalTweets = count($tweets);
        $totalPages = ceil($totalTweets / $perPage);

        return [
            'tweets' => array_slice($tweets, $offset, $perPage),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalTweets,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ],
        ];
    }
}
