<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests;

use PHPUnit\Framework\TestCase;
use Swap\Builder;
use Swap\Swap;

class BuilderTest extends TestCase
{
    public function testBuild()
    {
        $builder = new Builder();
        $this->assertInstanceOf(Swap::class, $builder->build());

        $builder = new Builder(['cache_ttl']);
        $this->assertInstanceOf(Swap::class, $builder->build());

        $builder = new Builder();
        $builder->add('fixer', ['access_key' => 'access_key']);
        $builder->add('open_exchange_rates', ['app_id' => 'secret']);
        $this->assertInstanceOf(Swap::class, $builder->build());
    }
}
