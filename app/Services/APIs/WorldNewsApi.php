<?php

namespace App\Services\APIs;

class WorldNewsApi extends BaseApi
{
    protected string $url;
    protected string $key;

    public function __construct()
    {
        parent::__construct();
        $this->url = config('services.worldnews.url');
        $this->key = config('services.worldnews.key');
    }

    public function fetchAndStore()
    {
        $searchFrom = now()->subHours(2)->format('Y-m-d H:i:s');
        $offset = 0;

        while (true) {
            try {
                $response = $this->client->get($this->url, [
                    'language' => 'en',
                    'earliest-publish-date' => $searchFrom,
                    'offset' => $offset,
                    'limit' => 100,
                    'api-key' => $this->key,
                ]);

                if (!$response->successful()) break;

                $data = $response->json();
                if (empty($data['news'])) break;

                $this->storeArticles($this->parseArticles($data['news']));
                $offset += 100;
            } catch (\Exception $e) {
                logger()->error('WorldNews API Error: ' . $e->getMessage());
                break;
            }
        }
    }

    private function parseArticles(array $articles)
    {
        return array_map(fn($article) => [
            'author' => $article['author'] ?? 'N/A',
            'title' => $article['title'],
            'description' => $article['text'] ?? null,
            'url' => $article['url'],
            'source' => 'worldnews',
            'published_at' => $article['publish_date'],
        ], $articles);
    }
}