import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';

const TweetApp = () => {
    const [userName, setUserName] = useState('');
    const [tweets, setTweets] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const fetchTweets = async (page: number = 1) => {
        if (!userName) {
            setError('Please enter a username');
            return;
        }

        setLoading(true);
        setError('');

        try {
            const response = await fetch(`/api/tweets?username=${userName}`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to fetch tweets');
            }
            const data = await response.json();
            setTweets(data);
        } catch (err) {
            setError('Failed to fetch tweets');
        } finally {
            setLoading(false);
        }
    };

    const formatDate = (dateString: string): string => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Twitter Feed</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="flex gap-4 mb-6">
                        <Input
                            type="text"
                            value={userName}
                            onChange={(e) => setUserName(e.target.value)}
                            placeholder="Enter username (e.g. joe_smith)"
                            className="flex-grow"
                        />
                        <Button onClick={() => fetchTweets(1)} disabled={loading}>
                            {loading ? 'Loading...' : 'Fetch Tweets'}
                        </Button>
                    </div>

                    {error && (
                        <Alert variant="destructive" className="mb-6">
                            <AlertDescription>{error}</AlertDescription>
                        </Alert>
                    )}

                    <div className="space-y-4">
                        {tweets.length === 0 && (
                            <p className="text-center text-gray-500">
                                { loading ? 'Searching...' : 'No tweets to display'}
                            </p>
                        )}

                        {tweets.map((tweet) => (
                            <Card key={tweet.id}>
                                <CardContent className="pt-6">
                                    <div className="flex justify-between items-start mb-2">
                                        <span className="font-medium">@{tweet.user.userName}</span>
                                        <span className="text-sm text-gray-500">
                      {formatDate(tweet.createdAt)}
                    </span>
                                    </div>
                                    <p className="text-gray-700">{tweet.text}</p>
                                </CardContent>
                            </Card>
                        ))}

                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

export default TweetApp;
