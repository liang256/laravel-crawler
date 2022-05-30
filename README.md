# About Laravel-Crawler

## Features
- Facebook OAuth login
- AstroCrawler is a tool to crawl [this website](http://astro.click108.com.tw/)'s daily astro fortunes

## Usage of AstroCrawlerService

It is very simpler to use.

This sample crawl today's fortune of 12 astros and return a `collection` of `AstroFortune` models.

``` php
$service = new AstroCrawlerService();
$models = $service->crawl();
```

To save them into DB, you can achieve it by easily using `each` provided by `collection`.
``` php
$models->each(funciton ($model) {
    $model->save();
});
```

### setType
There are 3 types of this crawler

>0. fortune of a date in this week
>1. fortune of this week
>2. fortune of this month

To set the type of fortune to crawl
``` php
$models = $service->setType(2)->crawl();
```

### setAstros & setAstro
By default, this service will crawl all 12 astros, but we can specific which astros to crawl by these 2 functions.

`AstroCrawlerService` provides const variables to easily choose astros.

``` php
use AstroCrawlerService as ACS
$models = $service->setType(2)->setAstro(ACS::GEMINI)->crawl();
```

``` php
$models = $service->setType(2)->setAstros([0, 2, 4])->crawl();
```

### setDate

This function only be useful when "fortune of a date" (type 0)

``` php
$date = today()->format('Y-m-d');
$models = $service->setType(0)->setDate($date)->crawl();
```

### select

Select what columns to crawl like SQL language. By default, this service will crawl all 4 columns

>general: 整體運勢</br>
>love: 愛情運勢</br>
>career: 事業運勢</br>
>wealth: 財運運勢

``` php
$models = $service->setType(2)->select(['love', 'general'])->crawl();
```
