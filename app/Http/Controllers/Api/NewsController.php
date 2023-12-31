<?php

namespace App\Http\Controllers\Api;

use App\Classes\NewsTransformer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use stdClass;

class NewsController extends Controller
{
    public function fetchNews(Request $request)
    {
        $request->validate([
            'date' => 'sometimes|date',
            'category' => 'sometimes|string',
            'author' => 'sometimes|string',
            'source' => 'sometimes|in:newsapi,guardian,nytimes',
            'keyword' => 'sometimes|string',
            'offset' => 'sometimes|string',
            'limit' => 'sometimes|string'
        ]);

        // Get the authenticated user
        if (Auth::check() || Auth::user()) {
            $user = Auth::user();
        } else {
            $user = null;
        }

        $preferences = isset($user) ? $user->preferences : new stdClass();

        // Get or create the user's preferences with a default source of 'newsapi'
        $selectedSource = $request->input('source', $preferences->source ?? 'guardian');
        $category = $request->input('category', $preferences->category ?? '');
        $author = $request->input('author', $preferences->author ?? '');
        $keyword = $request->input('keyword') ?? '';
        $date = $request->input('date');
        $offset = $request->input('offset') ?? 0;
        $limit = $request->input('limit') ?? 24;

        // Customize the API endpoint based on the selected source
        switch ($selectedSource) {
            case 'newsapi':
                $apiEndpoint = 'https://newsapi.org/v2/top-headlines';
                $apiKey = config('services.newsapi.api_key');
                $queryParams = [
                    'apiKey' => $apiKey,
                    'q' => $keyword,
                    'page' => floor($offset / $limit) + 1,
                    'pageSize' => $limit,
                ];

                if (!empty($category)) {
                    echo 'sdf';
                    $queryParams['category'] = $category;
                }

                break;

            case 'guardian':
                $apiEndpoint = 'https://content.guardianapis.com/search';
                $apiKey = config('services.guardian.api_key');
                $queryParams = [
                    'api-key' => $apiKey,
                    'q' => $keyword,
                    'show-fields' => "thumbnail",
                    'page' => floor($offset / $limit) + 1,
                    'page-size' => $limit
                ];

                if (!empty($category)) {
                    $queryParams['section'] = $category;
                }
                break;

            case 'nytimes':
                $apiEndpoint = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
                $apiKey = config('services.nytimes.api_key');
                $queryParams = [
                    'api-key' => $apiKey,
                    'q' => $keyword,
                    'page' => floor($offset / $limit) + 1
                ];

                if (!empty($category)) {
                    $queryParams['fq'] = "section_name:\"{$category}\"";
                }

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
            $transformedResponse = NewsTransformer::transform($news, $selectedSource);
            return response()->json(['news' => $transformedResponse]);
        }

        // Handle the case where the request was not successful
        return response()->json(['error' => 'Failed to fetch news', 'queryParams' => $queryParams], 500);
    }
}
