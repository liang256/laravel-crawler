<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CrawlerService;

class AstroCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:crawler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->crawlerService = new CrawlerService();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $date = today()->format('Y-m-d');
        $models = $this->crawlerService->setDate('2021-07-01')->fetch();
        // dd($models);
        // printf("name: %s\n", $astro->name);

        
        printf("end\n");
    }
}
