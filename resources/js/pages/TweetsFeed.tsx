import React, {useEffect, useState} from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer } from 'recharts';

interface User {
    userName: string;
}

interface Tweet {
    id: string;
    text: string;
    createdAt: string;
    user: User;
}

interface PaginationData {
    total_pages: number;
}

interface TweetAnalytics {
    totalTweets: number;
    maxDaysBetweenTweets: number;
    mostNumberOfTweetsPerDay: number;
    mostPopularHashtag: string;
    numberOfTweetsPerDay: Record<string, number>;
}

interface TweetResponse {
    tweets: Tweet[];
    pagination: PaginationData;
    analytics: TweetAnalytics;
}

const TweetApp = () => {
    const [userName, setUserName] =  useState('');
    const [tweets, setTweets] = useState<Tweet[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [pagination, setPagination] = useState<PaginationData | null>(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [analytics, setAnalytics] = useState<TweetAnalytics | null>(null);

    const fetchTweets = async (page: number = 1) => {
        if (!userName) {
            setError('Please enter a username');
            return;
        }

        setLoading(true);
        setError('');

        try {
            const response = await fetch(`/api/tweets?username=${userName}&page=${page}&per_page=${perPage}`);
            if (!response.ok) {
                const errorData = await response.json();

                handleError(response, errorData);
            }
            const data: TweetResponse = await response.json();
            setTweets(data.tweets);
            setPagination(data.pagination);
            setCurrentPage(page);
            setAnalytics(data.analytics);
        } catch (err: any) {
            setError(err instanceof Error ? err.message : 'An unexpected error occurred');
            setTweets([]);
            setPagination(null);
            setAnalytics(null);
            setCurrentPage(1);
        } finally {
            setLoading(false);
        }
    };

    const handlePageChange = (newPage: number) :void => {
        if (newPage >= 1 && pagination && newPage <= pagination?.total_pages) {
            fetchTweets(newPage);
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

    // very basic pooling :D
    useEffect(() => {
        if (tweets.length > 0) {
            const interval = setInterval(() => {
                fetchTweets(currentPage);
            }, 1000 * 60 * 30); // 30 minutes

            return () => clearInterval(interval);
        }
    }, [currentPage]);

    const handleError = (response: Response, errorData: any) => {
        if (response.status === 400) {
            if (errorData.message?.toLowerCase().includes('username')) {
                setError('Please enter a valid username.');
            } else {
                throw new Error(errorData.message || 'Failed to fetch tweets');
            }
            throw new Error('Please enter a valid username');
        } else if (response.status === 404) {
            throw new Error('We are unable to find any data, please check the username and try again');
        } else if (response.status === 429) {
            throw new Error('Too many requests, please try again in a few minutes');
        } else if (response.status >= 500) {
            throw new Error('An error occurred, please try again in a few minutes and contact support if the issue persists');
        } else {
            throw new Error(errorData.message || 'Failed to fetch tweets');
        }
    }

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Twitter Feed</CardTitle>
                </CardHeader>
                <CardContent>
                    <form className="flex gap-4 mb-6" onSubmit={(e) => e.preventDefault()}>
                        <Input
                            type="text"
                            defaultValue={userName}
                            onChange={(e) => error && setError('')}
                            onBlur={(e) => setUserName(e.target.value)}
                            placeholder="Enter username (e.g. joe_smith)"
                            className="flex-grow"
                        />
                        <Button onClick={() => fetchTweets(1)} disabled={loading}>
                            {loading ? 'Loading...' : 'Fetch Tweets'}
                        </Button>
                    </form>

                    {error && (
                        <Alert variant="destructive" className="mb-6">
                            <AlertDescription>{error}</AlertDescription>
                        </Alert>
                    )}

                    {analytics && (
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

                        {pagination && pagination.total_pages > 1 && (
                            <div className="flex items-center justify-between mt-6">
                                <Button
                                    variant="outline"
                                    onClick={() => handlePageChange(currentPage - 1)}
                                    disabled={currentPage === 1 || loading}
                                    className="flex items-center gap-2"
                                >
                                    <ChevronLeft className="h-4 w-4" />
                                    Previous
                                </Button>

                                <div className="text-sm text-gray-600">
                                    Page {currentPage} of {pagination.total_pages}
                                </div>

                                <Button
                                    variant="outline"
                                    onClick={() => handlePageChange(currentPage + 1)}
                                    disabled={currentPage === pagination.total_pages || loading}
                                    className="flex items-center gap-2"
                                >
                                    Next
                                    <ChevronRight className="h-4 w-4" />
                                </Button>
                            </div>
                        )}

                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

export default TweetApp;
