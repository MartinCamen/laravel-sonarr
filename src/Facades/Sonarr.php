<?php

namespace MartinCamen\LaravelSonarr\Facades;

use Illuminate\Support\Facades\Facade;
use MartinCamen\ArrCore\Actions\SystemActions;
use MartinCamen\ArrCore\Actions\WantedActions;
use MartinCamen\ArrCore\Domain\Download\DownloadItemCollection;
use MartinCamen\ArrCore\Domain\Media\Series;
use MartinCamen\ArrCore\Domain\System\SystemSummary;
use MartinCamen\Sonarr\Actions\CalendarActions;
use MartinCamen\Sonarr\Actions\CommandActions;
use MartinCamen\Sonarr\Actions\EpisodeActions;
use MartinCamen\Sonarr\Actions\EpisodeFileActions;
use MartinCamen\Sonarr\Actions\HistoryActions;
use MartinCamen\Sonarr\Sonarr as CoreSonarr;
use MartinCamen\Sonarr\Testing\SonarrFake;

/**
 * @method static DownloadItemCollection downloads()
 * @method static array<int, Series> series()
 * @method static Series seriesById(int $id)
 * @method static SystemActions system()
 * @method static SystemSummary systemSummary()
 * @method static EpisodeActions episode()
 * @method static EpisodeFileActions episodeFile()
 * @method static CalendarActions calendar()
 * @method static HistoryActions history()
 * @method static WantedActions wanted()
 * @method static CommandActions command()
 *
 * @see CoreSonarr
 */
class Sonarr extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param array<string, mixed> $responses
     */
    public static function fake(array $responses = []): SonarrFake
    {
        static::swap($sonarrFake = new SonarrFake($responses));

        return $sonarrFake;
    }

    protected static function getFacadeAccessor(): string
    {
        return 'sonarr';
    }
}
