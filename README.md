# PSR-6 Cache Health Checker

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
use OAT\Library\HealthCheckCache\RamseyCacheKeyGenerator;

$healthChecker = new HealthChecker();

// your cache pool instance that implements the PSR-6 CacheItemPoolInterface
$psr6CachePool = ...

$cacheChecker = new CacheChecker($psr6CachePool, new RamseyCacheKeyGenerator());

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
