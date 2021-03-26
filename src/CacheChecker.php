<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\HealthCheckCache;

use OAT\Library\HealthCheck\Checker\CheckerInterface;
use OAT\Library\HealthCheck\Result\CheckerResult;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;

class CacheChecker implements CheckerInterface
{
    private const IDENTIFIER = 'cache';

    /** @var CacheItemPoolInterface */
    private $cacheItemPool;

    /** @var CacheKeyGeneratorInterface */
    private $cacheKeyGenerator;

    public function __construct(CacheItemPoolInterface $cacheItemPool, CacheKeyGeneratorInterface $cacheKeyGenerator = null)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheKeyGenerator = $cacheKeyGenerator ?? new UuidCacheKeyGenerator();
    }

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function check(): CheckerResult
    {
        try {
            $keyAndValue = $this->cacheKeyGenerator->generate();

            $item = $this->cacheItemPool->getItem($keyAndValue);
            $item->set($keyAndValue);
            $item->expiresAfter(30);

            if (!$this->cacheItemPool->save($item)) {
                return new CheckerResult(false, sprintf('Writing item %s failed', $keyAndValue));
            }

            $item = $this->cacheItemPool->getItem($keyAndValue);
            if (!$item->isHit()) {
                return new CheckerResult(false, sprintf('Missed hit on item %s', $keyAndValue));
            }

            if ($item->get() !== $keyAndValue) {
                return new CheckerResult(false, sprintf('Mismatched value on item %s', $keyAndValue));
            }

            if (!$this->cacheItemPool->deleteItem($keyAndValue)) {
                return new CheckerResult(false, sprintf('Removing item %s failed', $keyAndValue));
            }

            return new CheckerResult(
                true,
                sprintf('Success on writing, reading and deleting cache item %s', $keyAndValue)
            );
        } catch (CacheException $exception) {
            return new CheckerResult(false, $exception->getMessage());
        }
    }
}
