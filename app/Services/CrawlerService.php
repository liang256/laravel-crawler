<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\DailyAstro;
use Exception;
use Illuminate\Support\Collection;

class CrawlerService
{
    /** @var Client  */
    private $client;

    /** @var array astro code  */
    private $astro_list;

    /** @var array date  */
    private $date_list;

    public function __construct()
    {
        $this->client = app(Client::class);
        $this->date_list = [today()->format('Y-m-d')];
        $this->astro_list = range(0, 11);
    }

    /**
     * @param string $path
     * @return Crawler
     */
    public function crawl(int $astro_code, string $date): DailyAstro
    {
        $path = "http://astro.click108.com.tw/daily.php";
        $path .= sprintf("?iAstro=%d", $astro_code);
        $path .= sprintf("&iAcDay=%s", $date);
        $content = $this->client->get($path)->getBody()->getContents();
        // dump($path);
        // TODO: check if has page

        $crawler = new Crawler();
        $crawler->addHtmlContent($content);

        // TODO: 檢查結果是否為請求日期，若不符合回傳無當日運勢
        
        $parsed_fortune = [];
        $content = $crawler->filterXPath(('//div[contains(@class, "TODAY_CONTENT")]'));
        $astro_name = preg_replace('/(今日|解析)/', '', $content->filter('h3')->html());

        $content->filterXPath('//p')->each(
            function ($node) use (&$parsed_fortune) {
                $span = $node->filterXPath('//span');

                // if span, the element is about score, parse score
                if ($span->getNode(0)) {
                    $splited = preg_split('/★/', $span->text());
                    $parsed_fortune[$splited[0]] = [];
                    // fortune score
                    $parsed_fortune[$splited[0]]['score'] = count($splited) - 1;
                }

                // if not span, the content is fortune instruciton
                if (!$span->getNode(0)) {
                    // 運勢說明
                    $parsed_fortune[array_key_last($parsed_fortune)]['fortune'] = $node->html();
                }
            }
        );
        // dd($parsed_fortune);
        $model = DailyAstro::where([
            'name' => $astro_name,
            'date' => $date
        ])->firstOrNew();
        
        $model->name = $astro_name;
        $model->date = $date;
        $model->general_score = $parsed_fortune["整體運勢"]['score'];
        $model->general_fortune = $parsed_fortune["整體運勢"]['fortune'];
        $model->love_score = $parsed_fortune["愛情運勢"]['score'];
        $model->love_fortune = $parsed_fortune["愛情運勢"]['fortune'];
        $model->career_score = $parsed_fortune["事業運勢"]['score'];
        $model->career_fortune = $parsed_fortune["事業運勢"]['fortune'];
        $model->wealth_score = $parsed_fortune["財運運勢"]['score'];
        $model->wealth_fortune = $parsed_fortune["財運運勢"]['fortune'];
  
        return $model;
    }

    public function setDate(string $date)
    {
        $this->date_list = [$date];
        return $this;
    }

    public function setAstros(array $input_astros)
    {
        $this->astro_list = array_intersect($this->astro_list, $input_astros);
        return $this;
    }

    public function setAstro(int $input_astro)
    {
        if (!in_array($input_astro, range(0, 11))) {
            throw new Exception('Astro code must between 0 and 11');
        }
        $this->astro_list = [$input_astro];
        return $this;
    }

    public function fetch(): Collection
    {
        $models = [];
        foreach ($this->astro_list as $astro_code) {
            foreach ($this->date_list as $date) {
                $model = $this->crawl($astro_code, $date);

                try {
                    $model->save();
                } catch (\Throwable $th) {
                    \Log::error($th->getMessage());
                    continue;
                }
    
                $models[] = $model;
            }
        }
        return collect($models);
    }

    public function get()
    {
        $models = [];
        foreach ($this->astro_list as $astro_code) {
            foreach ($this->date_list as $date) {
                $model = $this->crawl($astro_code, $date);
                $models[] = $model;
            }
        }
        return collect($models);
    }
}