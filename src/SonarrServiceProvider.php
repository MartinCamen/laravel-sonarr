<?php

namespace MartinCamen\LaravelSonarr;

use MartinCamen\ArrCore\Client\RestClientInterface;
use MartinCamen\LaravelSonarr\Client\LaravelRestClient;
use MartinCamen\Sonarr\Client\SonarrApiClient;
use MartinCamen\Sonarr\Config\SonarrConfiguration;
use MartinCamen\Sonarr\Sonarr;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SonarrServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sonarr')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            SonarrConfiguration::class,
            fn($app): SonarrConfiguration => SonarrConfiguration::fromArray($app['config']->get('sonarr', [])),
        );

        $this->app->singleton(
            RestClientInterface::class,
            fn($app): RestClientInterface => new LaravelRestClient($app->make(SonarrConfiguration::class)),
        );

        // Internal API client
        $this->app->singleton(SonarrApiClient::class, function ($app): SonarrApiClient {
            $config = $app->make(SonarrConfiguration::class);

            return new SonarrApiClient(
                host: $config->host,
                port: $config->port,
                apiKey: $config->apiKey,
                useHttps: $config->useHttps,
                timeout: $config->timeout,
                urlBase: $config->urlBase,
                apiVersion: $config->apiVersion,
                restClient: $app->make(RestClientInterface::class),
            );
        });

        // Sonarr SDK - the primary interface using php-arr-core domain models
        $this->app->singleton('sonarr', fn($app): Sonarr => new Sonarr($app->make(SonarrApiClient::class)));

        $this->app->alias('sonarr', Sonarr::class);
    }
}
