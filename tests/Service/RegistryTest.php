<?php

declare(strict_types=1);

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Swap\Service\Registry;
use Exchanger\Service\Registry as ExchangerRegistry;

final class RegistryTest extends TestCase
{
    #[Test]
    #[DataProvider('serviceProviders')]
    public function coreServices(string $name, string $class): void
    {
        $registry = new Registry();

        $this->assertTrue($registry->has($name));
        $this->assertEquals($class, $registry->get($name));
    }

    /**
     * @return list<array{string, string}>
     */
    public static function serviceProviders(): array
    {
        $data = [];
        /** @var array<string, string> $services */
        $services = ExchangerRegistry::getServices();

        foreach ($services as $name => $class) {
            $data[] = [$name, $class];
        }

        return $data;
    }

    #[Test]
    public function register(): void
    {
        $registry = new Registry();

        $registry->register('foo', 'Foo');
        $this->assertTrue($registry->has('foo'));
        $this->assertEquals('Foo', $registry->get('foo'));

        $callable = function (): void {};
        $registry->register('foo', $callable);
        $this->assertTrue($registry->has('foo'));
        $this->assertEquals($callable, $registry->get('foo'));
    }
}
