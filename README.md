# PSR-6 Cache Health Checker

> [PSR-6](https://www.php-fig.org/psr/psr-6/) cache health checker for the [Health checks PHP library](https://github.com/oat-sa/lib-health-check)

## Table of contents
- [Installation](#installation)
- [Usage](#usage)
- [Tests](#tests)

## Installation

```console
$ composer require oat-sa/lib-health-check-cache
```

## Usage

This library provides a [CacheChecker](src/CacheChecker.php) checker in charge to check if the provided cache pool is reachable.

```php
<?php

declare(strict_types=1);

use OAT\Library\HealthCheck\HealthChecker;
use OAT\Library\HealthCheckCache\CacheChecker;
use OAT\Library\HealthCheckCache\UuidCacheKeyGenerator;
use Psr\Cache\CacheItemPoolInterface;

$healthChecker = new HealthChecker();

/** @var CacheItemPoolInterface $cache */
$cacheChecker = new CacheChecker($cache, new UuidCacheKeyGenerator());

$results = $healthChecker
    ->registerChecker($cacheChecker)
    ->performChecks();
```

> **_Note_**: The built-in [RamseyCacheKeyGenerator](src/RamseyCacheKeyGenerator.php) can accept a custom prefix. If not provided, a default prefix will be used.

> **_Note_**: If you need a custom logic to generate the cache key, create your own key generator by implementing [CacheKeyGeneratorInterface](src/CacheKeyGeneratorInterface.php)
> Make sure your generated key is unique enough.
## Tests

To run tests:
```console
$ vendor/bin/phpunit
```
> **_Note_**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
