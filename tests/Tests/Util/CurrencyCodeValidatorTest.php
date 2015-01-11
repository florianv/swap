<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Util;

use Swap\Util\CurrencyCodeValidator;

class CurrencyCodeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_returns_true_when_code_valid()
    {
        $this->assertTrue(CurrencyCodeValidator::isValid('EUR'));
    }

    /**
     * @test
     */
    function it_returns_false_when_code_invalid()
    {
        $this->assertFalse(CurrencyCodeValidator::isValid('XXL'));
    }
}
