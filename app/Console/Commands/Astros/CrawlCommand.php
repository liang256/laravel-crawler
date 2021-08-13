<?php

namespace App\Console\Commands\Astros;

use Illuminate\Console\Command;
use App\Services\AstroCrawlerService as ACS;

class CrawlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:crawl {type=0 : The type of astro fortune range}
        {-- date= : Specific dates in this week to crawl}
        {-- astros=* : Specific astros to crawl}';

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
        $this->crawlerService = new ACS();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // dd($this->option('date'));
        
        $type = $this->argument('type');
        if ($type !== ACS::DAY && !is_null($this->option('date'))) {
            $this->error("Only type 0 accept a date given.");
            return 1;
        }

        if (is_null($this->option('date'))) {
            $date = today()->format('Y-m-d');
        }

        $models = $this->crawlerService->setType($type)->crawl();

        try {
            $models->each(
                function ($item) {
                    $item->save();
                }
            );
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
            return 1;
        }

        $this->info(sprintf("Astro crawled."));
        return 0;
    }
}
