<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use App\Models\UserPreferences;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserPreferencesController extends BaseController
{
    // Update the user's preferred source, category, and author
    public function updatePreferences(Request $request)
    {
        // Validate the request parameters
        $request->validate([
            'source' => 'required|in:newsapi,guardian,nytimes',
            'category' => 'sometimes|string',
            'author' => 'sometimes|string',
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Get or create the user's preferences
        $preferences = UserPreferences::firstOrNew(['user_id' => $user->id]);

        // Update the preferences
        $preferences->source = $request->input('source');
        $preferences->category = $request->input('category');
        $preferences->author = $request->input('author');

        // Save the preferences
        $preferences->save();

        $success['source'] = $preferences->source;
        $success['category'] = $preferences->category;
        $success['author'] = $preferences->author;

        return $this->sendResponse($success, 'Preferences updated successfully');
    }


    // Get the available categories by source
    public function getCategoriesBySource(Request $request)
    {
        // Validate the source parameter
        $request->validate([
            'source' => 'required|in:newsapi,guardian,nytimes',
        ]);

        // Get the source from the request
        $source = $request->input('source');

        // Fetch categories based on the source
        $categories = [];

        if ($source == 'guardian') {
            // Get the Guardian API key from your configuration
            $apiKey = config('services.guardian.api_key');

            // Make a request to the Guardian API
            $response = Http::get("https://content.guardianapis.com/sections?api-key={$apiKey}");

            // Check if the request was successful
            if ($response->successful()) {
                $data = $response->json('response.results');

                // Extract 'id' and 'webTitle' from each result
                $categories = collect($data)->map(function ($section) {
                    return [
                        'id' => $section['id'],
                        'name' => $section['webTitle'],
                    ];
                });
            } else {
                // Handle the case where the request was not successful
                return $this->sendError('guardian-error', ['error' => 'Failed to fetch Guardian sections'], 500);
            }
        } else {
            // Read the JSON file
            $jsonPath = resource_path('json/categories.json');
            $categoriesData = json_decode(file_get_contents($jsonPath), true);

            // Fetch categories based on the selected source
            $categories = $categoriesData[$source] ?? [];
        }

        return response()->json($categories);
    }
}
