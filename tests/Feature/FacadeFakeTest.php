<?php

use MartinCamen\ArrCore\Domain\Download\DownloadItemCollection;
use MartinCamen\ArrCore\Domain\Media\Series;
use MartinCamen\ArrCore\Domain\System\SystemSummary;
use MartinCamen\LaravelSonarr\Facades\Sonarr;
use MartinCamen\Sonarr\Testing\Factories\DownloadFactory;
use MartinCamen\Sonarr\Testing\Factories\SeriesFactory;
use MartinCamen\Sonarr\Testing\Factories\SystemStatusFactory;
use MartinCamen\Sonarr\Testing\SonarrFake;

describe('Facade Fake', function (): void {
    it('can swap the facade for a fake', function (): void {
        $fake = Sonarr::fake();

        expect($fake)->toBeInstanceOf(SonarrFake::class)
            ->and(Sonarr::getFacadeRoot())->toBeInstanceOf(SonarrFake::class);
    });

    it('can assert nothing was called', function (): void {
        Sonarr::fake();

        Sonarr::getFacadeRoot()->assertNothingCalled();
    });
});

describe('Downloads', function (): void {
    it('can get downloads', function (): void {
        Sonarr::fake();

        $downloads = Sonarr::downloads();

        expect($downloads)->toBeInstanceOf(DownloadItemCollection::class);
        Sonarr::getFacadeRoot()->assertCalled('downloads');
    });

    it('can provide custom response for downloads', function (): void {
        Sonarr::fake([
            'downloads' => [
                'page'         => 1,
                'pageSize'     => 10,
                'totalRecords' => 2,
                'records'      => DownloadFactory::makeMany(2),
            ],
        ]);

        $downloads = Sonarr::downloads();

        expect($downloads)->toBeInstanceOf(DownloadItemCollection::class)
            ->and($downloads->count())->toBe(2);
    });

    it('download collection provides filter methods', function (): void {
        Sonarr::fake();

        $downloads = Sonarr::downloads();

        expect($downloads->active())->toBeInstanceOf(DownloadItemCollection::class)
            ->and($downloads->completed())->toBeInstanceOf(DownloadItemCollection::class)
            ->and($downloads->failed())->toBeInstanceOf(DownloadItemCollection::class)
            ->and($downloads->sortByPriority())->toBeInstanceOf(DownloadItemCollection::class);
    });
});

describe('Series', function (): void {
    it('can get all series', function (): void {
        Sonarr::fake();

        $series = Sonarr::series();

        expect($series)->toBeArray()
            ->not->toBeEmpty()
            ->each->toBeInstanceOf(Series::class);
        Sonarr::getFacadeRoot()->assertCalled('series');
    });

    it('can get series by id', function (): void {
        Sonarr::fake();

        $series = Sonarr::seriesById(1);

        expect($series)->toBeInstanceOf(Series::class);
        Sonarr::getFacadeRoot()->assertCalled('seriesById');
        Sonarr::getFacadeRoot()->assertCalledWith('seriesById', ['id' => 1]);
    });

    it('can provide custom response for series', function (): void {
        Sonarr::fake([
            'series' => [
                SeriesFactory::make(1, ['title' => 'Breaking Bad', 'year' => 2008]),
                SeriesFactory::make(2, ['title' => 'The Wire', 'year' => 2002]),
            ],
        ]);

        $series = Sonarr::series();

        expect($series)->toHaveCount(2)
            ->and($series[0]->title)->toBe('Breaking Bad')
            ->and($series[0]->year)->toBe(2008)
            ->and($series[1]->title)->toBe('The Wire')
            ->and($series[1]->year)->toBe(2002);
    });

    it('can provide custom response for single series', function (): void {
        Sonarr::fake([
            'seriesById' => SeriesFactory::make(5, [
                'title' => 'The Sopranos',
                'year'  => 1999,
            ]),
        ]);

        $series = Sonarr::seriesById(5);

        expect($series->title)->toBe('The Sopranos')
            ->and($series->year)->toBe(1999);
    });

    it('can provide custom response for specific series id', function (): void {
        Sonarr::fake([
            'seriesById/10' => SeriesFactory::make(10, ['title' => 'Series Ten']),
            'seriesById/20' => SeriesFactory::make(20, ['title' => 'Series Twenty']),
        ]);

        $series10 = Sonarr::seriesById(10);
        $series20 = Sonarr::seriesById(20);

        expect($series10->title)->toBe('Series Ten')
            ->and($series20->title)->toBe('Series Twenty');
    });
});

describe('System Status', function (): void {
    it('can get system status', function (): void {
        Sonarr::fake();

        $systemSummary = Sonarr::systemSummary();

        expect($systemSummary)->toBeInstanceOf(SystemSummary::class);
        Sonarr::getFacadeRoot()->assertCalled('systemSummary');
    });

    it('can provide custom response for system status', function (): void {
        Sonarr::fake([
            'systemSummary' => SystemStatusFactory::make([
                'version'  => '4.0.0.0',
                'branch'   => 'develop',
                'isDocker' => true,
            ]),
        ]);

        $systemSummary = Sonarr::systemSummary();

        expect($systemSummary->version)->toBe('4.0.0.0')
            ->and($systemSummary->branch)->toBe('develop');
    });
});

describe('Fake Assertions', function (): void {
    it('can assert method was called', function (): void {
        Sonarr::fake();

        Sonarr::systemSummary();

        Sonarr::getFacadeRoot()->assertCalled('systemSummary');
    });

    it('can assert method was not called', function (): void {
        Sonarr::fake();

        Sonarr::systemSummary();

        Sonarr::getFacadeRoot()->assertNotCalled('series');
    });

    it('can assert method was called specific times', function (): void {
        Sonarr::fake();

        Sonarr::systemSummary();
        Sonarr::systemSummary();
        Sonarr::systemSummary();

        Sonarr::getFacadeRoot()->assertCalledTimes('systemSummary', 3);
    });

    it('can get all recorded calls', function (): void {
        Sonarr::fake();

        Sonarr::systemSummary();
        Sonarr::series();

        $calls = Sonarr::getFacadeRoot()->getCalls();

        expect($calls)->toHaveKey('systemSummary')
            ->toHaveKey('series')
            ->and($calls['systemSummary'])->toHaveCount(1)
            ->and($calls['series'])->toHaveCount(1);
    });

    it('can set response after creation', function (): void {
        $fake = Sonarr::fake();

        $fake->setResponse('series', [
            SeriesFactory::make(1, ['title' => 'Custom Series']),
        ]);

        $series = Sonarr::series();

        expect($series)->toHaveCount(1)
            ->and($series[0]->title)->toBe('Custom Series');
    });
});
