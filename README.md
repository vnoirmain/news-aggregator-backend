# News Aggregator Backend

Welcome to the News Aggregator Backend, a Laravel project that serves as the backend for a news aggregator website. This project allows users to retrieve news from various sources, apply filters, and customize their news preferences based on their credentials.

## Overview

The primary features of this project include:

1. **News Retrieval**: Fetches news from various sources via APIs.
2. **Filtering**: Users can search for news using filters such as author, date, and more.
3. **User Preferences**: Registered users can customize their news preferences.

## Running the Project

To run the project locally, follow these steps:

1. Clone the repository:

   ```bash
   git clone https://github.com/vnoirmain/news-aggregator-backend.git
   ```

2. Run the project:

```bash
docker-compose up
```

The project will be accessible at [http://localhost:8000](http://localhost:8000).

## Advanced Implementation (Future Enhancement)

The current implementation retrieves news directly from external APIs on each request. However, for a more efficient and scalable solution, consider implementing the following:

1. **Data Scraping**: Implement a backend process for scraping news data from websites and storing it in the local news database.

2. **Elasticsearch and phpFastCache**: Utilize Elasticsearch for efficient data retrieval, especially for handling large datasets. Integrate phpFastCache for improved caching mechanisms.

## Contribution

Feel free to contribute to this project by opening issues or submitting pull requests.
