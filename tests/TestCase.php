<?php

namespace MartinCamen\LaravelSonarr\Tests;

use MartinCamen\LaravelSonarr\Facades\Sonarr;
use MartinCamen\LaravelSonarr\SonarrServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SonarrServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Sonarr' => Sonarr::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sonarr.host', 'localhost');
        $app['config']->set('sonarr.port', 8989);
        $app['config']->set('sonarr.api_key', 'test-api-key');
        $app['config']->set('sonarr.use_https', false);
        $app['config']->set('sonarr.timeout', 30);
        $app['config']->set('sonarr.url_base', '');
        $app['config']->set('sonarr.api_version', 'v3');
    }
}
