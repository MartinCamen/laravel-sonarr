# Laravel Sonarr

Laravel integration for the [Sonarr PHP SDK](https://github.com/martincamen/sonarr-php), providing a seamless experience for interacting with Sonarr using unified domain models from [php-arr-core](https://github.com/martincamen/php-arr-core).

A [Laravel Radarr integration](https://github.com/martincamen/laravel-radarr) is also available.

Also available:
- [Laravel Radarr integration](https://github.com/martincamen/laravel-radarr)

## Features

- Unified API using canonical domain models from `php-arr-core`
- Type-safe interactions with Sonarr
- Laravel facade with full IDE autocompletion
- Testing utilities for mocking responses
- Automatic service discovery via Laravel's package auto-discovery

## Requirements

- PHP 8.3+
- Laravel 10.0+, 11.0+ or 12.0+

## Installation

```bash
composer require martincamen/laravel-sonarr
```

The package will auto-register its service provider in Laravel.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="MartinCamen\LaravelSonarr\SonarrServiceProvider"
```

Add the following environment variables to your `.env` file:

```env
SONARR_HOST=localhost
SONARR_PORT=8989
SONARR_API_KEY=your-api-key
SONARR_USE_HTTPS=false
SONARR_TIMEOUT=30
SONARR_URL_BASE=
```

### Configuration Options

| Option             | Description                                           | Default     |
|--------------------|-------------------------------------------------------|-------------|
| `SONARR_HOST`      | Hostname or IP address of your Sonarr server          | `localhost` |
| `SONARR_PORT`      | Port number for your Sonarr server                    | `8989`      |
| `SONARR_API_KEY`   | Your Sonarr API key (Settings > General > Security)   | -           |
| `SONARR_USE_HTTPS` | Use HTTPS for connections                             | `false`     |
| `SONARR_TIMEOUT`   | Request timeout in seconds                            | `30`        |
| `SONARR_URL_BASE`  | URL base for reverse proxy subpaths (e.g., `/sonarr`) | -           |

## Usage

### Using the Facade

The `Sonarr` facade provides access to the SDK client, returning canonical domain models from `php-arr-core`:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;

// Get all active downloads
$downloads = Sonarr::downloads();

// Get all series
$series = Sonarr::series();

// Get a specific series by ID
$show = Sonarr::seriesById(1);

// Get system summary
$summary = Sonarr::systemSummary();
```

### Dependency Injection

You can also inject `Sonarr` directly:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;

class SeriesController
{
    public function __construct(private Sonarr $sonarr) {}

    public function index()
    {
        return view('series.index', ['series' => $this->sonarr->series()]);
    }
}
```

## Working with Downloads

The `downloads()` method returns a `DownloadItemCollection` containing all active downloads:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;
use MartinCamen\ArrCore\Domain\Download\DownloadItemCollection;

/** @var DownloadItemCollection $downloads */
$downloads = Sonarr::downloads();

// Check if there are any downloads
if ($downloads->isEmpty()) {
    echo 'No active downloads';
}

// Get the count
echo "Active downloads: {$downloads->count()}";

// Filter by status
$activeDownloads = $downloads->active();
$completedDownloads = $downloads->completed();
$failedDownloads = $downloads->failed();

// Get downloads with errors
$withErrors = $downloads->withErrors();

// Sort by priority (errors first, then active, then waiting)
$sorted = $downloads->sortByPriority();

// Get total size and progress
$totalSize = $downloads->totalSize();
$remaining = $downloads->totalRemaining();
$progress = $downloads->totalProgress();

echo "Overall progress: {$progress->percentage()}%";
```

## Working with Series

The `series()` method returns an array of `Series` domain objects:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;
use MartinCamen\ArrCore\Domain\Media\Series;

// Get all series
/** @var Series[] $series */
$series = Sonarr::series();

foreach ($series as $show) {
    echo "{$show->title} ({$show->year})";

    // Check series status
    if ($show->hasEnded()) {
        echo ' - Ended';
    }

    // Access metadata
    echo "Size on disk: {$show->sizeOnDisk?->formatted()}";
}

// Get a specific series
/** @var Series $show */
$show = Sonarr::seriesById(1);

echo $show->title;
```

## System information

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;

// Get system status information
$system = Sonarr::system()->status();

echo $system->version;

// Get system health information
$health = Sonarr::system()->health();

foreach ($health->warnings() as $warning) {
    echo $warning->type . ': ' . $warning->message;
}

// Get disk space information
$diskSpace = Sonarr::system()->diskSpace();

echo $diskSpace->totalFreeSpace();

// Get system tasks information
Sonarr::system()->tasks();
Sonarr::system()->task(id: 1);

// Get available backups
Sonarr::system()->backups();
```

## System summary

The `systemSummary()` method returns a `SystemSummary` object with combined status information and health information:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;

$status = Sonarr::systemSummary();

echo "Sonarr Version: {$status->version}";
echo "Branch: {$status->branch}";
echo "Runtime: {$status->runtimeVersion}";
echo "OS: {$status->osName}";

// Check system health
if ($status->isHealthy) {
    echo 'System is healthy';
} else {
    echo "System has {$status->issueCount()} issues:";

    foreach ($status->healthIssues as $issue) {
        echo "- [{$issue->type->value}] {$issue->message}";
    }
}

// Get uptime
echo "Uptime: {$status->uptime()}";
```

## Domain Models

All responses use canonical domain models from `php-arr-core`, providing a unified interface across all *arr services:

| Model                    | Description                                           |
|--------------------------|-------------------------------------------------------|
| `DownloadItemCollection` | Collection of active downloads                        |
| `DownloadItem`           | Individual download with status, progress, size       |
| `Series`                 | TV series with metadata, status, and file information |
| `SystemSummary`          | System summary (status & health issues)               |
| `HealthIssue`            | Individual health check issue                         |

### Value Objects

The domain models use strongly-typed value objects:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;

$downloads = Sonarr::downloads();

foreach ($downloads as $download) {
    // FileSize value object
    $size = $download->size;
    echo $size->bytes();        // Raw bytes
    echo $size->formatted();    // "1.5 GB"

    // Progress value object
    $progress = $download->progress;
    echo $progress->value();       // 0.75
    echo $progress->percentage();  // 75.0
    echo $progress->formatted();   // "75%"

    // Duration value object
    $eta = $download->estimatedTime;
    echo $eta?->formatted();  // "2h 15m"
}
```

## Testing

### Using the Fake

The package provides `SonarrFake` for testing:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;

class SeriesTest extends TestCase
{
    public function testDisplaysDownloads(): void
    {
        // Create a fake instance
        $fake = Sonarr::fake();

        // Make request
        $response = $this->get('/downloads');

        // Assert the method was called
        $fake->assertCalled('downloads');
        $response->assertOk();
    }

    public function testGetsSpecificSeries(): void
    {
        $fake = Sonarr::fake();

        // Make request that calls seriesById(5)
        $this->get('/series/5');

        // Assert called with specific parameters
        $fake->assertCalledWith('seriesById', ['id' => 5]);
    }

    public function testNothingWasCalled(): void
    {
        $fake = Sonarr::fake();

        // No API calls made
        $this->get('/about');

        $fake->assertNothingCalled();
    }
}
```

### Custom Responses

You can provide custom responses to the fake:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;
use MartinCamen\Sonarr\Testing\Factories\DownloadFactory;
use MartinCamen\Sonarr\Testing\Factories\SeriesFactory;
use MartinCamen\Sonarr\Testing\Factories\SystemStatusFactory;

public function testWithCustomSeries(): void
{
    Sonarr::fake([
        'series' => SeriesFactory::makeMany(10),
    ]);

    $response = $this->get('/series');

    $response->assertOk();
    $response->assertViewHas('series');
}

public function testWithCustomDownloads(): void
{
    Sonarr::fake([
        'downloads' => [
            'page' => 1,
            'pageSize' => 10,
            'totalRecords' => 2,
            'records' => DownloadFactory::makeMany(2),
        ],
    ]);

    $response = $this->get('/downloads');

    $response->assertOk();
}

public function testWithCustomSystemStatus(): void
{
    Sonarr::fake([
        'systemSummary' => SystemStatusFactory::make([
            'version' => '4.0.0.0',
            'isProduction' => true,
        ]),
    ]);

    $response = $this->get('/system');

    $response->assertSee('4.0.0.0');
}
```

### Assertion Methods

The fake provides several assertion methods:

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;

$fake = Sonarr::fake();

// Assert a method was called
$fake->assertCalled('downloads');

// Assert a method was not called
$fake->assertNotCalled('series');

// Assert a method was called with specific parameters
$fake->assertCalledWith('seriesById', ['id' => 5]);

// Assert a method was called a specific number of times
$fake->assertCalledTimes('downloads', 3);

// Assert nothing was called
$fake->assertNothingCalled();

// Get all recorded calls
$calls = $fake->getCalls();
```

## Example: Building a Dashboard

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;
use MartinCamen\ArrCore\Domain\Media\Series;

class DashboardController extends Controller
{
    public function index()
    {
        // Get system status
        $summary = Sonarr::systemSummary();

        // Get active downloads
        $downloads = Sonarr::downloads();

        // Get all series
        $series = Sonarr::series();

        // Filter series for display
        $endedSeries = array_filter(
            $series,
            fn(Series $show) => $show->hasEnded(),
        );

        return view('dashboard', [
            'summary'       => $summary,
            'downloads'     => $downloads->sortByPriority(),
            'downloadCount' => $downloads->count(),
            'seriesCount'   => count($series),
            'endedCount'    => count($endedSeries),
        ]);
    }
}
```

## Error Handling

```php
use MartinCamen\LaravelSonarr\Facades\Sonarr;
use MartinCamen\Sonarr\Exceptions\{
    AuthenticationException,
    SonarrConnectionException,
    NotFoundException,
};

try {
    $series = Sonarr::seriesById(999);
} catch (AuthenticationException $e) {
    // Invalid API key
    return back()->with('error', 'Invalid Sonarr API key');
} catch (NotFoundException $e) {
    // Series not found
    abort(404, 'Series not found');
} catch (SonarrConnectionException $e) {
    // Connection error
    logger()->error('Could not connect to Sonarr: ' . $e->getMessage());

    return back()->with('error', 'Sonarr server unavailable');
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

Built on top of the [Sonarr PHP SDK](https://github.com/martincamen/sonarr-php) and [php-arr-core](https://github.com/martincamen/php-arr-core).
