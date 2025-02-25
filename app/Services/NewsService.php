<?php

namespace App\Services;

use App\Services\APIs\NewsApi;
use App\Services\APIs\GuardianApi;
use App\Services\APIs\WorldNewsApi;

class NewsService
{
    private $newsApi;
    private $guardianApi;
    private $worldNewsApi;

    public function __construct()
    {
        $this->newsApi = new NewsApi();
        $this->guardianApi = new GuardianApi();
        $this->worldNewsApi = new WorldNewsApi();
    }

    public function fetchAndStoreArticles()
    {
        $this->newsApi->fetchAndStore();
        $this->guardianApi->fetchAndStore();
        $this->worldNewsApi->fetchAndStore();
    }
}
