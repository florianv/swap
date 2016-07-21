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

    /**
     * @test
     */
    public function createQuotationRequestByFactory()
    {
        $pair = CurrencyPair::createFromString('USD/CZK');
        $dateTime = new \DateTime('2016-01-01');
        $request = QuotationRequest::create($pair, $dateTime);
        $this->assertInstanceOf('Swap\Model\QuotationRequest', $request);
    }

    public function validCurrencyPairsAndDateTimes()
    {
        return [
            [CurrencyPair::createFromString('USD/GBP'), new \DateTime('2016-02-28')],
            [CurrencyPair::createFromString('EUR/USD'), null], // latest
        ];
    }
}
