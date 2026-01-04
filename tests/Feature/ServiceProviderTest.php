<?php

declare(strict_types=1);

use MartinCamen\ArrCore\Client\RestClientInterface;
use MartinCamen\LaravelSonarr\Client\LaravelRestClient;
use MartinCamen\LaravelSonarr\Facades\Sonarr;
use MartinCamen\Sonarr\Config\SonarrConfiguration;
use MartinCamen\Sonarr\Sonarr as CoreSonarr;

it('registers the SonarrConfig singleton', function (): void {
    $sonarrConfiguration = app(SonarrConfiguration::class);

    expect($sonarrConfiguration)->toBeInstanceOf(SonarrConfiguration::class)
        ->and($sonarrConfiguration->host)->toBe('localhost')
        ->and($sonarrConfiguration->port)->toBe(8989)
        ->and($sonarrConfiguration->apiKey)->toBe('test-api-key')
        ->and($sonarrConfiguration->useHttps)->toBeFalse()
        ->and($sonarrConfiguration->timeout)->toBe(30)
        ->and($sonarrConfiguration->urlBase)->toBe('')
        ->and($sonarrConfiguration->apiVersion)->toBe('v3');
});

it('registers the RestClientInterface singleton', function (): void {
    $restClient = app(RestClientInterface::class);

    expect($restClient)->toBeInstanceOf(LaravelRestClient::class);
});

it('registers Core Sonarr as primary singleton', function (): void {
    $sonarr = app('sonarr');

    expect($sonarr)->toBeInstanceOf(CoreSonarr::class);
});

it('resolves Core Sonarr from the facade', function (): void {
    expect(Sonarr::getFacadeRoot())->toBeInstanceOf(CoreSonarr::class);
});

it('resolves Core Sonarr via type hint', function (): void {
    $sonarr = app(CoreSonarr::class);

    expect($sonarr)->toBeInstanceOf(CoreSonarr::class);
});
