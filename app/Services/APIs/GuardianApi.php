<?php

namespace App\Services\APIs;

class GuardianApi extends BaseApi
{
    protected string $url;
    protected string $key;

    public function __construct()
    {
        parent::__construct();
        $this->url = config('services.guardian.url');
        $this->key = config('services.guardian.key');
    }

    public function fetchAndStore()
    {
        $searchFrom = now()->subHours(2)->toIso8601String();
        $page = 1;
        $totalPages = null;

        while (true) {
            try {
                $response = $this->client->get($this->url, [
                    'from-date' => $searchFrom,
                    'type' => 'article',
                    'page' => $page,
                    'page-size' => 50,
                    'api-key' => $this->key,
                ]);

                if (!$response->successful()) break;

                $data = $response->json();

                // Check if results are empty
                if (empty($data['response']['results'])) break;

                $this->storeArticles($this->parseArticles($data['response']['results']));

                // Determine total pages from the first response
                if ($totalPages === null) {
                    $totalPages = $data['response']['pages'];
                }

                // Stop if current page is the last page
                if ($page >= $totalPages) {
                    break;
                }

                $page++;
            } catch (\Exception $e) {
                logger()->error('GuardianApi API Error: ' . $e->getMessage());
                break;
            }
        }
    }

    private function parseArticles(array $articles)
    {
        return array_map(fn($article) => [
            'title' => $article['webTitle'],
            'description' => $article['webTitle'],
            'url' => $article['webUrl'],
            'source' => 'guardian',
            'published_at' => $article['webPublicationDate'],
        ], $articles);
    }
}