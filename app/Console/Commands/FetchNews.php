<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsService;

class FetchNews extends Command
{
    protected $signature = 'news:fetch';
    protected $description = 'Fetch and store latest news articles';

    public function __construct(private NewsService $newsService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->newsService->fetchAndStoreArticles();
        $this->info('News articles fetched successfully.');
    }
}