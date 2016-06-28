<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Model;

use Swap\Model\QuotationRequest;
use Swap\Model\CurrencyPair;

class QuotationRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider validCurrencyPairsAndDateTimes
     */
    public function createsQuotationRequestWithPairAndTimestamp($pair, $dateTime)
    {
        $r = new QuotationRequest($pair, $dateTime);
        $this->assertEquals($r->getCurrencyPair(), $pair);
        $this->assertEquals($r->getDateTime(), $dateTime);
    }

    public function validCurrencyPairsAndDateTimes()
    {
        return [
            [ new CurrencyPair('USD', 'GBP'), new \DateTime('2016-02-28') ],
            [ new CurrencyPair('EUR', 'USD'), null ] // latest
        ];
    }
}
