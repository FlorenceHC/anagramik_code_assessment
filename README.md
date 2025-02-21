# React + Laravel code assessment

## Your Task

You need to implement a Laravel backend that would act as a proxy to the [TweetsAPIService](https://app.codescreen.com/api/assessments/tweets). It should expose a single endpoint which lists tweets by username. The endpoint should be consumed by a React app, that will fetch and display the tweets in the browser.

---

## External API Details

The external CodeScreen Tweets API is available at:
GET https://app.codescreen.com/api/assessments/tweets

### Query Parameter

- **userName** — The username whose tweets need to be fetched. For the purpose of the test, only the userName **joe_smith** will return tweets.

### Authentication

Pass your API token in the Authorization header using the Bearer scheme:

- **Token**: `8c5996d5-fb89-46c9-8821-7063cfbc18b1`

#### Example cURL Request

```
curl -H "Authorization: Bearer 8c5996d5-fb89-46c9-8821-7063cfbc18b1" \
     "https://app.codescreen.com/api/assessments/tweets?userName=joe_smith"
```

When you send an HTTP GET request to the endpoint, the response will be a 200 OK, which includes a body containing a list of tweet data in JSON format.

#### Example Response

``` 
[
  {
    "id": "52f83d7c-ad2c-4ca0-b742-b03bc27f0c96",
    "createdAt": "2017-12-01T11:12:42",
    "text": "Chrome or Firefox? #Browsers",
    "user": {
      "id": "75343078-b5dd-306f-a3f9-8203a3915144",
      "userName": "joe_smith"
    }
  },
  {
    "id": "5f52e882-36a5-4460-a33b-e834b406650e",
    "createdAt": "2017-12-02T12:17:52",
    "text": "Bought a real Christmas tree, smells a lot more christmassy! #Xmas",
    "user": {
      "id": "75343078-b5dd-306f-a3f9-8203a3915144",
      "userName": "joe_smith"
    }
  }
]
```

The id field represents the unique id for the tweet. The createdAt field contains the time at which the tweet was published, in ISO-8601 extended offset date-time format. You can assume all date-times are in the same timezone.
The user field contains a JSON object which is made up of the unique id and name of the user who published the tweet.


## Backend (Laravel) Requirements


### Functionality

- Validation
    - Ensure the username parameter is provided; return an appropriate error if it’s missing.
- External API Call
    - Use Laravel’s HTTP client (or Guzzle) to call the external CodeScreen Tweets API with:
        - The query parameter userName.
        - The Authorization header: ```Bearer 8c5996d5-fb89-46c9-8821-7063cfbc18b1.```
- Response
    - Return the tweet data to the client in JSON format, preserving the structure as provided by the external API.

### Testing

- Write unit tests using Laravel’s testing framework (Pest or phpUnit) to cover:
    - Successful retrieval of tweets.
    - Missing username parameter
    - Invalid bearer token sent
    - Structure of tweets should match the one in "Example response"


## Frontend (React) Requirements

- User Interface
    - Create a simple React application able to connect with the Laravel backend.
    - Include:
        - An input field where users can enter a username. *(note that only the username **joe_smith** will return tweets. Any other username will return an empty response .)*
        - A button to trigger the tweet fetch.
        - A display area to list the tweets returned from the Laravel backend.
            - For each tweet, display at least:
                - Tweet text
                - Creation date
                - Username
            - Gracefully handle an empty response scenario (in case of no tweets)
- Error Handling:
    - Show meaningful error messages in the UI if the backend returns an error (e.g., missing username or network issues).

## Libraries & Tools:
- Use any third-party libraries you find helpful (e.g., Axios for React HTTP calls, a UI component library, etc.). However, ensure your choices are justified by clarity and maintainability.
- Regarding CSS, you may choose you're own approach. You can use plain CSS or a framework like Tailwind, Bootstrap or similar.


## Optional (Advanced)
The following features are *not required* for the completion of the task but they are a great opportunity to showcase even better your skills.

#### Caching:
Implement caching so that for each unique username, the external API is called at most once during a set period (for example, 30 minutes). Subsequent requests within this time frame should return cached data. Caching could be implemented in the backend, frontend, or both.

#### Pagination:
If there are many tweets, consider adding pagination to both the API response and the UI.

#### Polling
- Implement a live tweet feed.

#### Additional features and Tests (for username joe_smith)
- Most tweets for any day (it should equal to **10**)
- Longest tweet by id (it should equal to **0c2dc961-a0ae-470e-81a6-8320504dae14**)
- Most days between tweets (it should equal to **120**)
- Most popular hash tag (it should equal to **#WorldCup2018**)

#### Additional Analytics
- Display basic tweet analytics such as:
- Total number of tweets.
- Group tweets by day and show counts per day.

#### Deployment
Include a simple Docker setup or deployment instructions that allow the project to be run locally without hassle.

## Good luck!

---
# Just run this in your terminal and then access the app on this url http://localhost:8000/
```
docker-compose up -d --build
```
---


     
