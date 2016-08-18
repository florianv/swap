<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Provider;

use Swap\Exception\Exception;
use Swap\Model\CurrencyPair;
use Swap\Provider\XigniteProvider;

class XigniteProviderTest extends AbstractProviderTestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_on_response_error()
    {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=GBPAWG&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=token';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/Xignite/error.json');

        $provider = new XigniteProvider($this->getHttpAdapterMock($uri, $content), 'token');
        $caught = false;

        try {
            $provider->fetchRate(new CurrencyPair('GBP', 'AWG'));
        } catch (Exception $e) {
            $caught = true;
            $this->assertEquals('Error message', $e->getMessage());
        }

        $this->assertTrue($caught);
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=GBPAWG&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=token';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/Xignite/success.json');

        $provider = new XigniteProvider($this->getHttpAdapterMock($uri, $content), 'token');
        $rate = $provider->fetchRate(new CurrencyPair('GBP', 'AWG'));

        $this->assertEquals('2.982308', $rate->getValue());
        $this->assertEquals(new \DateTime('2014-05-11 21:22:00', new \DateTimeZone('UTC')), $rate->getDate());
    }
}
