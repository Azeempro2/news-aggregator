<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Article;
use Carbon\Carbon;

class NewsService_bkp
{
    protected $apis = [
        'newsapi' => [
            'url' => 'https://newsapi.org/v2/everything',
            'key' => 'fdf4729d913b46fdae5802c5bf1448e5'
        ],
        'guardian' => [
            'url' => 'https://content.guardianapis.com/search',
            'key' => '5fd85823-2a46-4dc6-94af-345e9448b9e8'
        ],
        'worldnews' => [
            'url' => 'https://api.worldnewsapi.com/search-news',
            'key' => 'c4fa0da5fdb342ffa76ba3ff9b1bc444'
        ]
    ];

    public function fetchAndStoreArticles()
    {
        $searchFrom = Carbon::now()->subHour(5)->toIso8601String();

        foreach ($this->apis as $source => $config) {
            $response = Http::retry(3, 100)
                ->get($config['url'], $this->buildQueryParams($source, $searchFrom));

//            if ($source == "worldnews"){
//                dd($response->json());
//            }
            if ($response->successful()) {
                $articles = $this->parseArticles($source, $response->json());
                $this->storeArticles($articles);
            }
        }
    }

    private function buildQueryParams($source, $searchFrom)
    {
        switch ($source) {
            case 'guardian':
                return [
                    'from-date' => $searchFrom,
                    'type' => 'article',
                    'api-key' => $this->apis[$source]['key']
                ];
            case 'newsapi':
                return [
                    'sources' => 'bbc-news,cnn,reuters',
                    'from' => $searchFrom,
                    'apiKey' => $this->apis[$source]['key']
                ];
            case 'worldnews':
                return [
                    'language' => 'en',
                    'earliest-publish-date' => Carbon::now()->subHour(5)->format('Y-m-d H:i:s'),
                    'api-key' => $this->apis[$source]['key']
                ];
            default:
                return [];
        }
    }

    private function parseArticles($source, $data)
    {
        $articles = [];
        switch ($source) {
            case 'guardian':
                foreach ($data['response']['results'] ?? [] as $article) {
                    $articles[] = [
                        'title' => $article['webTitle'],
                        'description' => $article['webTitle'],
                        'url' => $article['webUrl'],
                        'source' => $source,
                        'published_at' => Carbon::parse($article['webPublicationDate'])
                    ];
                }
                break;
            case 'newsapi':
                foreach ($data['articles'] ?? [] as $article) {
                    $articles[] = [
                        'author' => $article['author'] ?? 'N/A',
                        'title' => $article['title'],
                        'description' => $article['description'] ?? null,
                        'url' => $article['url'],
                        'source' => $source,
                        'published_at' => $article['publishedAt']
                    ];
                }
                break;
            case 'worldnews':
                foreach ($data['news'] ?? [] as $article) {
                    $articles[] = [
                        'author' => $article['author'] ?? 'N/A',
                        'title' => $article['title'],
                        'description' => $article['text'] ?? null,
                        'url' => $article['url'],
                        'source' => $source,
                        'published_at' => Carbon::parse($article['publish_date'])
                    ];
                }
                break;
        }
        return $articles;
    }

    private function storeArticles($articles)
    {
        foreach ($articles as $article) {
            Article::updateOrCreate(['url' => $article['url']], $article);
        }
    }
}
