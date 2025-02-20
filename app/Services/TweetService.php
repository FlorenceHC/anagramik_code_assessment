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

    public function getTweets(string $userName): array
    {
        $cacheKey = "tweets.{$userName}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userName) {
            $response = Http::withToken(self::API_TOKEN)
                ->get(self::API_URL, [
                    'userName' => $userName
                ]);

            if ($response->failed()) {
                throw new \Exception('Failed to fetch tweets: ' . $response->status());
            }

            return $response->json();
        });
    }
}
