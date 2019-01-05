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

namespace Swap\Tests;

use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Swap\Builder;
use Swap\Swap;

class BuilderTest extends TestCase
{
    public function testBuild()
    {
        $builder = new Builder();
        $builder->useHttpClient(new Client());

        $this->assertInstanceOf(Swap::class, $builder->build());

        $builder = new Builder(['cache_ttl']);
        $builder->useHttpClient(new Client());

        $this->assertInstanceOf(Swap::class, $builder->build());

        $builder = new Builder();
        $builder->useHttpClient(new Client());

        $builder->add('fixer', ['access_key' => 'access_key']);
        $builder->add('open_exchange_rates', ['app_id' => 'secret']);
        $this->assertInstanceOf(Swap::class, $builder->build());
    }
}
