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

use PHPUnit\Framework\TestCase;
use Swap\Service\Registry;
use Exchanger\Service\Registry as ExchangerRegistry;

class RegistryTest extends TestCase
{
    /**
     * @dataProvider serviceProviders
     */
    public function testCoreServices($name, $class)
    {
        $registry = new Registry();

        $this->assertTrue($registry->has($name));
        $this->assertEquals($class, $registry->get($name));
    }

    public function serviceProviders()
    {
        $data = [];
        $services = ExchangerRegistry::getServices();

        foreach ($services as $name => $class) {
            $data[] = [$name, $class];
        }
    }

    public function testRegister()
    {
        $registry = new Registry();

        $registry->register('foo', 'Foo');
        $this->assertTrue($registry->has('foo'));
        $this->assertEquals('Foo', $registry->get('foo'));

        $callable = function () {
        };
        $registry->register('foo', $callable);
        $this->assertTrue($registry->has('foo'));
        $this->assertEquals($callable, $registry->get('foo'));
    }
}
