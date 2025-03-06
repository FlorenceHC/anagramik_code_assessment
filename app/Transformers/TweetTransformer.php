<?php

namespace App\Transformers;

class TweetTransformer
{
    public function isValid(array $tweet): bool
    {
        $missingFields = $this->getMissingFields($tweet);

        if (!empty($missingFields)) {
            // Notify slack channel about the missing fields
            // or report to the monitoring system

            return false;
        }

        if (!strtotime($tweet['createdAt'])) {
            // Notify slack channel about the missing fields
            // or report to the monitoring system

            return false;
        }

        return true;
    }

    public function transform(array $data): array
    {
        return array_map(function ($tweet) {

            if (!$this->isValid($tweet)) {
                return;
            }

            return [
                'id'        => $tweet['id'],
                'text'      => $tweet['text'],
                'createdAt' => $tweet['createdAt'],
                'user'      => [
                    'userName' => $tweet['user']['userName'],
                ],
            ];
        }, $data);
    }

    private function getMissingFields(array $tweet): array
    {
        $missing = [];

        if (!isset($tweet['id'])) {
            $missing[] = 'id';
        }
        if (!isset($tweet['text'])) {
            $missing[] = 'text';
        }
        if (!isset($tweet['createdAt'])) {
            $missing[] = 'createdAt';
        }
        if (!isset($tweet['user']['userName'])) {
            $missing[] = 'user.userName';
        }

        return $missing;
    }
}
