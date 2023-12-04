<?php

namespace App\Classes;

class NewsTransformer
{
  public static function transform($apiResponse, $source)
  {
    // Transform the $apiResponse into a standardized format
    switch ($source) {
      case 'newsapi':
        if (isset($apiResponse['articles']) && is_array($apiResponse['articles'])) {
          return array_map(function ($article) {
            return [
              'title' => $article['title'] ?? '',
              'content' => $article['content'] ?? '',
              'author' => $article['author'] ?? '',
              'webUrl' => $article['url'] ?? '',
              'thumbnail' => $article['urlToImage'] ?? '',
              'publishedAt' => $article['publishedAt'] ?? ''
            ];
          }, $apiResponse['articles']);
        }
        return [];
        break;
      case 'nytimes':
        if (isset($apiResponse['response']['docs']) && is_array($apiResponse['response']['docs'])) {
          return array_map(function ($article) {
            return [
              'title' => $article['abstract'] ?? '',
              'content' => $article['lead_paragraph'] ?? '',
              'author' => $article['author'] ?? '',
              'webUrl' => $article['uri'] ?? '',
              'thumbnail' => is_array($article['multimedia']) ? isset($article['multimedia'][0]) ? 'https://nytimes.com/' . $article['multimedia'][0]['url']
                : '' : '',
              'publishedAt' => $article['publishedAt'] ?? ''
            ];
          }, $apiResponse['response']['docs']);
        }

        return [];
        break;
      case 'guardian':
        if (isset($apiResponse['response']['results']) && is_array($apiResponse['response']['results'])) {
          return array_map(function ($article) {
            return [
              'title' => $article['webTitle'] ?? '',
              'content' => $article['lead_paragraph'] ?? '',
              'author' => $article['author'] ?? '',
              'webUrl' => $article['webUrl'] ?? '',
              'thumbnail' => isset($article['fields']) ? $article['fields']['thumbnail'] : '',
              'publishedAt' => $article['publishedAt'] ?? ''
            ];
          }, $apiResponse['response']['results']);
        }

        return [];
        break;
      default:
        return [];
        break;
    }
  }
}
