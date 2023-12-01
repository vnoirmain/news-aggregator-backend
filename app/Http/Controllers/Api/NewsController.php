<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    public function fetchNews(Request $request) {
        $request->validate([
            'date' => 'sometimes|date',
            'category' => 'sometimes|string',
            'author' => 'sometimes|string',
            'source' => 'sometimes|in:newsapi,guardian,nytimes',
            'keyword' => 'sometimes|string',
        ]);

        // Get the authenticated user
        $user = $request->user();
        $preferences = $user->preferences;
        
        // Get or create the user's preferences with a default source of 'newsapi'
        $selectedSource = $request->input('source', $preferences->source ?? 'newsapi');
        $category = $request->input('category', $preferences->category);
        $author = $request->input('author', $preferences->author);
        $keyword = $request->input('keyword');
        $date = $request->input('date');

        // Customize the API endpoint based on the selected source
        switch ($selectedSource) {
            case 'newsapi':
                $apiEndpoint = 'https://newsapi.org/v2/top-headlines';
                $apiKey = config('services.newsapi.api_key');
                $queryParams = [
                    'apiKey' => $apiKey,
                    'category' => $category,
                    'q' => $keyword,
                ];
                break;

            case 'guardian':
                $apiEndpoint = 'https://content.guardianapis.com/search';
                $apiKey = config('services.guardian.api_key');
                $queryParams = [
                    'api-key' => $apiKey,
                    'section' => $category,
                    'q' => $keyword,
                ];
                break;

            case 'nytimes':
                $apiEndpoint = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
                $apiKey = config('services.nytimes.api_key');
                $queryParams = [
                    'api-key' => $apiKey,
                    'fq' => "section_name:\"{$category}\"",
                    'q' => $keyword,
                ];
                break;

            default:
                return response()->json(['error' => 'Invalid source'], 400);
        }

        // Make the API request
        $response = Http::get($apiEndpoint, $queryParams);
        // return response()->json(['api' => $apiEndpoint, 'query' => $queryParams]);
        // Check if the request was successful
        if ($response->successful()) {
            $news = $response->json();
            return response()->json(['news' => $news]);
        }

        // Handle the case where the request was not successful
        return response()->json(['error' => 'Failed to fetch news'], 500);
    }
}
