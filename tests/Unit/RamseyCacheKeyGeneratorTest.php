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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\HealthCheckCache\Tests\Unit;

use OAT\Library\HealthCheckCache\RamseyCacheKeyGenerator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

class RamseyCacheKeyGeneratorTest extends TestCase
{
    private const TEST_UUID = '16dfb592-dbc2-4727-9fff-34db268fdddd';

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGenerateWithDefaultPrefix(): void
    {
        $this->prepareTestUuidFactory();

        $generator = new RamseyCacheKeyGenerator();

        self::assertSame(sprintf('oat-health-check-%s', self::TEST_UUID), $generator->generate());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGenerateWithCustomPrefix(): void
    {
        $this->prepareTestUuidFactory();

        $generator = new RamseyCacheKeyGenerator('custom-prefix');

        self::assertSame(sprintf('custom-prefix-%s', self::TEST_UUID), $generator->generate());
    }

    private function prepareTestUuidFactory(): void
    {
        $factory = new class() extends UuidFactory {
            public $uuid;

            public function uuid4(): UuidInterface
            {
                return $this->uuid;
            }
        };
        Uuid::setFactory($factory);

        $factory->uuid = Uuid::fromString(self::TEST_UUID);
    }
}
