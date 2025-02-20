import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ChevronLeft, ChevronRight } from 'lucide-react';

const TweetApp = () => {
    const [userName, setUserName] = useState('');
    const [tweets, setTweets] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [pagination, setPagination] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);

    const handleEnter = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter' && !loading && userName) {
            fetchTweets();
        }
    };

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
                throw new Error(errorData.error || 'Failed to fetch tweets');
            }
            const data = await response.json();
            setTweets(data.tweets);
            setPagination(data.pagination);
            setCurrentPage(page);
        } catch (err) {
            setError('Failed to fetch tweets');
        } finally {
            setLoading(false);
        }
    };

    const handlePageChange = (newPage: number) :void => {
        if (newPage >= 1 && newPage <= pagination?.total_pages) {
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
                            onKeyDown={handleEnter}
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
