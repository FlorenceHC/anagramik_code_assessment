<?php

namespace App\Http\Controllers\Tweets;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAllTweetsRequest;
use App\Services\TweetService;

class IndexController extends Controller
{
    public function __invoke(GetAllTweetsRequest $request, TweetService $tweetService)
    {
        try {
            $tweets    = $tweetService->getTweets($request->username, $request->page, $request->per_page);
            $analytics = $tweetService->getAnalytics($tweets['all_tweets']);

            return response()->json([
                'tweets'     => $tweets['tweets'],
                'pagination' => $tweets['pagination'],
                'analytics'  => $analytics,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getCode());
        }

    }
}
