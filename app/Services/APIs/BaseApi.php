<?php

namespace App\Services\APIs;

use Illuminate\Support\Facades\Http;
use App\Models\Article;
use Carbon\Carbon;

abstract class BaseApi
{
    protected $client;
    protected string $url;
    protected string $key;

    public function __construct()
    {
        $this->client = Http::retry(3, 100);
    }

    abstract public function fetchAndStore();

    protected function storeArticles(array $articles)
    {
        foreach ($articles as $article) {
            Article::updateOrCreate(['url' => $article['url']], $article);
        }
    }
}
