<?php

namespace Xxvnapi\Crawler\XxvnapiCrawler;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use Xxvnapi\Crawler\XxvnapiCrawler\Console\CrawlerScheduleCommand;
use Xxvnapi\Crawler\XxvnapiCrawler\Option;

class XxvnapiCrawlerServiceProvider extends SP
{
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return [];
    }

    public function register()
    {

        config(['plugins' => array_merge(config('plugins', []), [
            'hacoidev/xxvnapi-crawler' =>
            [
                'name' => 'X Crawler',
                'package_name' => 'htanh/3x-crawler',
                'icon' => 'la la-hand-grab-o',
                'entries' => [
                    ['name' => 'Crawler', 'icon' => 'la la-hand-grab-o', 'url' => backpack_url('/plugin/xxvnapi-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => backpack_url('/plugin/xxvnapi-crawler/options')],
                ],
            ]
        ])]);

        config(['logging.channels' => array_merge(config('logging.channels', []), [
            'xxvnapi-crawler' => [
                'driver' => 'daily',
                'path' => storage_path('logs/hacoidev/xxvnapi-crawler.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 7,
            ],
        ])]);

        config(['xxvnapi.updaters' => array_merge(config('xxvnapi.updaters', []), [
            [
                'name' => 'Xxvnapi Crawler',
                'handler' => 'Xxvnapi\Crawler\XxvnapiCrawler\Crawler'
            ]
        ])]);
    }

    public function boot()
    {
        $this->commands([
            CrawlerScheduleCommand::class,
        ]);

        $this->app->booted(function () {
            $this->loadScheduler();
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'xxvnapi-crawler');
    }

    protected function loadScheduler()
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->command('xxvnapi:plugins:xxvnapi-crawler:schedule')->cron(Option::get('crawler_schedule_cron_config', '*/10 * * * *'))->withoutOverlapping();
    }
}
