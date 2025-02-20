<?php

namespace App\Http\Controllers\Tweets;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAllTweetsRequest;
use App\Services\TweetService;

class IndexController extends Controller
{
    public function __invoke(GetAllTweetsRequest $request, TweetService $tweetService)
    {
        $tweets = $tweetService->getTweets($request->username);

        return response()->json($tweets);
    }
}
