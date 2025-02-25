<?php

namespace App\Services\APIs;

class NewsApi extends BaseApi
{
    protected string $url;
    protected string $key;

    public function __construct()
    {
        parent::__construct();
        $this->url = config('services.newsapi.url');
        $this->key = config('services.newsapi.key');
    }

    public function fetchAndStore()
    {
        $searchFrom = now()->subHours(2)->toIso8601String();
        $page = 1;

        while (true) {
            try {
                $response = $this->client->get($this->url, [
                    'sources' => 'bbc-news,cnn,reuters',
                    'from' => $searchFrom,
                    'page' => $page,
                    'pageSize' => 100,
                    'apiKey' => $this->key,
                ]);

                if (!$response->successful()) break;

                $data = $response->json();
                if (empty($data['articles'])) break;

                $this->storeArticles($this->parseArticles($data['articles']));
                $page++;
            } catch (\Exception $e) {
                logger()->error('NewsApi API Error: ' . $e->getMessage());
                break;
            }
        }
    }

    private function parseArticles(array $articles)
    {
        return array_map(fn($article) => [
            'author' => $article['author'] ?? 'N/A',
            'title' => $article['title'],
            'description' => $article['description'] ?? null,
            'url' => $article['url'],
            'source' => 'newsapi',
            'published_at' => $article['publishedAt'],
        ], $articles);
    }
}
