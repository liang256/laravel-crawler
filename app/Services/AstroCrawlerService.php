<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\AstroFortune;
use Exception;
use Illuminate\Support\Collection;

class AstroCrawlerService
{
    /** Available fields */
    public const AVAILABLE_FIELDS = [
        "general",
        "love",
        "career",
        "wealth"
    ];

    /** Fortune range type */
    public const DAY   = 0;
    public const WEEK  = 1;
    public const MONTH = 2;

    /** Asrto codes */
    public const ARIES       = 0;
    public const TAURUS      = 1;
    public const GEMINI      = 2;
    public const CANCER      = 3;
    public const LEO         = 4;
    public const VIRGO       = 5;
    public const LIBRA       = 6;
    public const SCORPIO     = 7;
    public const SAGITTARIUS = 8;
    public const CAPRICORN   = 9;
    public const AQUARIUS    = 10;
    public const PISCES      = 11;

    /** @var Client */
    private $client;

    /** @var array astro code */
    private $range_type;

    /** @var array astro code */
    private $astro_list;

    /** @var array date */
    private $date_list;

    /** @var array field */
    private $field_list;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://astro.click108.com.tw/daily.php'
        ]);
        $this->range_type = self::DAY;
        $this->date_list  = [today()->format('Y-m-d')];
        $this->astro_list = range(0, 11);
        $this->field_list = self::AVAILABLE_FIELDS;
    }

    /**
     * @param int $type
     * @return Collection
     */
    public function crawl()
    {
        $models = [];
        // Send request depends on range type
        foreach ($this->astro_list as $astro_code) {
            switch ($this->range_type) {
                case self::DAY:
                    foreach ($this->date_list as $date) {
                        $params = [
                            'iType' => self::DAY,
                            'iAstro' => $astro_code,
                            'iAcDay' => $date
                        ];
                        $model = $this->crawlAndParse($params);
                        $models[] = $model;
                    }
                    break;
                case self::WEEK:
                    $params = [
                        'iType' => self::WEEK,
                        'iAstro' => $astro_code,
                    ];
                    $models[] = $this->crawlAndParse($params);
                    break;
                case self::MONTH:
                    $params = [
                        'iType' => self::MONTH,
                        'iAstro' => $astro_code,
                    ];
                    $models[] = $this->crawlAndParse($params);
                    break;
            }
        }
        return collect($models);
    }

    /**
     * @param array $params query for GET request
     * @return AstroFortune
     */
    public function crawlAndParse(array $params)
    {
        // TODO check params has valid keys, iType, iAstro, iAcDay

        $uri = sprintf('?%s', http_build_query($params));

        $request = new Request('GET', $uri);

        try {
            $response = $this->client->send($request);
        } catch(\Throwable $th) {
            Log::error($th->getMessage());
        }

        $crawler = new Crawler($response->getBody()->getContents());
        
        // Parse time range
        switch ($params["iType"]) {
            case self::DAY:
                $date = $crawler->filter('select#iAcDay option:selected');

                // 檢查結果是否為請求日期，若不符合回傳無當日運勢
                if (isset($params['iAcDay']) && $params['iAcDay'] !== $date->text()) {
                    throw new Exception("No fortune for the date.");
                }

                $time_range = (is_null($date->getNode(0))) ? null : $date->text();
                break;
            case self::MONTH:
                $time_range = today()->format('Y-m');
                break;
            case self::WEEK:
                // TODO: parse real value of this week
                $time_range = 'THIS WEEK';
                break;
        }

        // Parse name
        $content = $crawler->filterXPath(('//div[contains(@class, "TODAY_CONTENT")]'));
        $astro_name = preg_replace('/(本周|本月|今日|解析)/', '', $content->filter('h3')->html());

        // Parse fortunes and score
        $parsed_fortune = [];
        $content->filterXPath('//p')->each(
            function ($node) use (&$parsed_fortune) {
                $span = $node->filterXPath('//span');

                // if span, the element is about score, parse score
                if ($span->getNode(0)) {
                    $splited = preg_split('/★/', $span->text());

                    // Translate zh-hant field to en field
                    $field = $this->translate($splited[0]);
                    $parsed_fortune[$field] = [];

                    // fortune score
                    $parsed_fortune[$field]['score'] = count($splited) - 1;
                    $parsed_fortune[$field]['fortune'] = "";
                }

                // if not span, the content is fortune instruciton
                if (!$span->getNode(0)) {
                    // 運勢說明
                    $parsed_fortune[array_key_last($parsed_fortune)]['fortune'] .= strip_tags($node->html());
                }
            }
        );

        // Assgin data to model
        $model = AstroFortune::where([
            'name' => $astro_name,
            'code' => $params['iAstro'],
            'type' => $this->range_type + 1,
            'time_range' => $time_range
        ])
        ->firstOrNew();
  
        $model->name = $astro_name;
        $model->code = $params['iAstro'];
        $model->type = $this->range_type + 1;
        $model->time_range = $time_range;

        foreach ($this->field_list as $field) {
            $model->{$field . "_score"} = $parsed_fortune[$field]['score'];
            $model->{$field . "_fortune"} = $parsed_fortune[$field]['fortune'];
        }
  
        return $model;
    }

    public function setDate(string $date)
    {
        $this->date_list = [$date];
        return $this;
    }

    public function setAstros(array $input_astros)
    {
        $astros = array_intersect($this->astro_list, $input_astros);

        if (empty($astros)) {
            throw new Exception('Non of input astros is valid.');
        }

        $this->astro_list = $astros;
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

    public function setType(int $input_type)
    {
        if (!in_array($input_type, range(0, 2))) {
            throw new Exception('Fortune range type must between 0 and 2.');
        }
        $this->range_type = $input_type;
        return $this;
    }

    public function select(array $input_fields)
    {
        $fields = array_intersect(self::AVAILABLE_FIELDS, $input_fields);
        if (empty($fields)) {
            throw new Exception('None of input fields are available.');
        }
        $this->field_list = $fields;
        return $this;
    }

    private function translate(string $input_field): string
    {
        $field = "";
        switch ($input_field) {
            case "整體運勢":
                $field = "general";
                break;
            case "愛情運勢":
                $field = "love";
                break;
            case "事業運勢":
                $field = "career";
                break;
            case "財運運勢":
                $field = "wealth";
                break;
            default:
                throw new Exception("Unknow field given.");
        }

        return $field;
    }

    public function reset()
    {
        $this->range_type = self::DAY;
        $this->date_list  = [today()->format('Y-m-d')];
        $this->astro_list = range(0, 11);
        $this->field_list = self::AVAILABLE_FIELDS;

        return $this;
    }
}