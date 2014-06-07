<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Integration;

use Swap\Adapter\Guzzle3Adapter;
use Guzzle\Http\Client;

class Guzzle3IntegrationTest extends AbstractIntegrationTestCase
{
    protected function setUp()
    {
        $this->adapter = new Guzzle3Adapter(new Client());
    }
}
