# PSR-6 Cache Health Checker

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-health-check-cache.svg?style=flat&label=release)](https://github.com/oat-sa/lib-health-check-cache/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://travis-ci.org/oat-sa/lib-health-check-cache.svg?branch=master)](https://travis-ci.org/oat-sa/lib-health-check-cache)
[![Coverage Status](https://coveralls.io/repos/github/oat-sa/lib-health-check-cache/badge.svg?branch=master)](https://coveralls.io/github/oat-sa/lib-health-check-cache?branch=master)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/lib-health-check-cache.svg)](https://packagist.org/packages/oat-sa/lib-health-check-cache)


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
$cacheChecker = new CacheChecker($cache);

$results = $healthChecker
    ->registerChecker($cacheChecker)
    ->performChecks();
```

> **_Note_**: The built-in [UuidCacheKeyGenerator](src/UuidCacheKeyGenerator.php) can accept a custom prefix. If not provided, a default prefix will be used.

> **_Note_**: If you need a custom logic to generate the cache key, create your own key generator by implementing [CacheKeyGeneratorInterface](src/CacheKeyGeneratorInterface.php)
> Make sure your generated key is unique enough.


## Tests

To run tests:
```console
$ vendor/bin/phpunit
```
> **_Note_**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
