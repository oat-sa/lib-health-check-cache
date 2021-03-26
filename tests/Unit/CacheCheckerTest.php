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

namespace OAT\Library\HealthCheckCache\Tests\Unit;

use Exception;
use OAT\Library\HealthCheckCache\CacheChecker;
use OAT\Library\HealthCheckCache\CacheKeyGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheCheckerTest extends TestCase
{
    private const CUSTOM_KEY = 'test-key';

    /** @var CacheChecker */
    private $subject;

    /** @var CacheItemPoolInterface|MockObject */
    private $cachePoolMock;

    /** @var CacheKeyGeneratorInterface|MockObject */
    private $keyGeneratorMock;

    protected function setUp(): void
    {
        $this->cachePoolMock = $this->createMock(CacheItemPoolInterface::class);
        $this->keyGeneratorMock = $this->createMock(CacheKeyGeneratorInterface::class);

        $this->subject = new CacheChecker($this->cachePoolMock, $this->keyGeneratorMock);
    }

    public function testGetIdentifier(): void
    {
        self::assertEquals('cache', $this->subject->getIdentifier());
    }

    public function testForSuccessCheckerResult(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock
            ->expects(self::once())
            ->method('set')
            ->with(self::CUSTOM_KEY);

        $cacheItemMock
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(30);

        $cacheItemMock
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItemMock
            ->expects(self::once())
            ->method('get')
            ->willReturn(self::CUSTOM_KEY);

        $this->keyGeneratorMock
            ->expects(self::once())
            ->method('generate')
            ->willReturn(self::CUSTOM_KEY);

        $this->cachePoolMock
            ->expects(self::exactly(2))
            ->method('getItem')
            ->with(self::CUSTOM_KEY)
            ->willReturn($cacheItemMock);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItemMock)
            ->willReturn(true);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('deleteItem')
            ->with(self::CUSTOM_KEY)
            ->willReturn(true);

        $result = $this->subject->check();

        self::assertTrue($result->isSuccess());
        self::assertEquals(
            sprintf('Success on writing, reading and deleting cache item %s', self::CUSTOM_KEY),
            $result->getMessage()
        );
    }

    public function testForFailedCheckerResultWhenExceptionThrown(): void
    {
        $cacheException = new class('custom error') extends Exception implements CacheException{};

        $this->keyGeneratorMock
            ->expects(self::once())
            ->method('generate')
            ->willReturn(self::CUSTOM_KEY);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('getItem')
            ->with(self::CUSTOM_KEY)
            ->willThrowException($cacheException);

        $result = $this->subject->check();

        self::assertFalse($result->isSuccess());
        self::assertEquals('custom error', $result->getMessage());
    }

    public function testForFailedCheckerResultWhenSavingItemFailed(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);

        $this->keyGeneratorMock
            ->expects(self::once())
            ->method('generate')
            ->willReturn(self::CUSTOM_KEY);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('getItem')
            ->with(self::CUSTOM_KEY)
            ->willReturn($cacheItemMock);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItemMock)
            ->willReturn(false);

        $result = $this->subject->check();

        self::assertFalse($result->isSuccess());
        self::assertEquals(sprintf('Writing item %s failed', self::CUSTOM_KEY), $result->getMessage());
    }

    public function testForFailedCheckerResultWhenItemHitMissed(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->keyGeneratorMock
            ->expects(self::once())
            ->method('generate')
            ->willReturn(self::CUSTOM_KEY);

        $this->cachePoolMock
            ->expects(self::exactly(2))
            ->method('getItem')
            ->with(self::CUSTOM_KEY)
            ->willReturn($cacheItemMock);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItemMock)
            ->willReturn(true);

        $result = $this->subject->check();

        self::assertFalse($result->isSuccess());
        self::assertEquals(sprintf('Missed hit on item %s', self::CUSTOM_KEY), $result->getMessage());
    }

    public function testForFailedCheckerResultWhenRetrievedValueIsNotTheSame(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItemMock
            ->expects(self::once())
            ->method('get')
            ->willReturn('wrong-value');

        $this->keyGeneratorMock
            ->expects(self::once())
            ->method('generate')
            ->willReturn(self::CUSTOM_KEY);

        $this->cachePoolMock
            ->expects(self::exactly(2))
            ->method('getItem')
            ->with(self::CUSTOM_KEY)
            ->willReturn($cacheItemMock);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItemMock)
            ->willReturn(true);

        $result = $this->subject->check();

        self::assertFalse($result->isSuccess());
        self::assertEquals(sprintf('Mismatched value on item %s', self::CUSTOM_KEY), $result->getMessage());
    }

    public function testForFailedCheckerResultWhenRemovingItemFailed(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock
            ->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItemMock
            ->expects(self::once())
            ->method('get')
            ->willReturn(self::CUSTOM_KEY);

        $this->keyGeneratorMock
            ->expects(self::once())
            ->method('generate')
            ->willReturn(self::CUSTOM_KEY);

        $this->cachePoolMock
            ->expects(self::exactly(2))
            ->method('getItem')
            ->with(self::CUSTOM_KEY)
            ->willReturn($cacheItemMock);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('save')
            ->with($cacheItemMock)
            ->willReturn(true);

        $this->cachePoolMock
            ->expects(self::once())
            ->method('deleteItem')
            ->with(self::CUSTOM_KEY)
            ->willReturn(false);

        $result = $this->subject->check();

        self::assertFalse($result->isSuccess());
        self::assertEquals(sprintf('Removing item %s failed', self::CUSTOM_KEY), $result->getMessage());
    }
}
