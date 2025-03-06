import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer } from 'recharts';

type TweetAnalytics = {
    totalTweets: number;
    maxDaysBetweenTweets: number;
    mostNumberOfTweetsPerDay: number;
    mostPopularHashtag: string;
    numberOfTweetsPerDay: Record<string, number>;
};

interface Props {
    analytics: TweetAnalytics;
}

function TweetAnalytics({ analytics }: Props) {
    return (
        <Card className="mb-6">
            <CardHeader>
                <CardTitle>Tweets Statistics</CardTitle>
            </CardHeader>
            <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <ul className="space-y-2">
                            <li>Total Tweets: {analytics.totalTweets}</li>
                            <li>Max Days Between Tweets: {analytics.maxDaysBetweenTweets}</li>
                            <li>Most Tweets in a Day: {analytics.mostNumberOfTweetsPerDay}</li>
                            <li>Most Popular Hashtag: {analytics.mostPopularHashtag}</li>
                        </ul>
                    </div>
                    <div className="h-48">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart
                                data={Object.entries(analytics.numberOfTweetsPerDay).map(([date, count]) => ({
                                    date,
                                    count
                                }))}
                            >
                                <XAxis dataKey="date" />
                                <YAxis />
                                <Tooltip />
                                <Bar dataKey="count" fill="#4f46e5" />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

export default TweetAnalytics;
