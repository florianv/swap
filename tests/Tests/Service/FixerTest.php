<?php
/*
 * This file is part of Swap.
 *
 * (c) Pascal Hofmann <mail@pascalhofmann.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Service;

use Swap\ExchangeRateQuery;
use Swap\HistoricalExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\Service\Fixer;

class FixerTest extends ServiceTestCase
{
    /**
     * @test
     */
    public function it_supports_all_queries()
    {
        $provider = new Fixer($this->getMock('Http\Client\HttpClient'));

        $this->assertTrue($provider->support(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'))));
        $this->assertTrue($provider->support(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\Exception
     */
    public function it_throws_an_exception_with_error_response()
    {
        $uri = 'https://api.fixer.io/latest?base=USD';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/Fixer/error.json');

        $provider = new Fixer($this->getHttpAdapterMock($uri, $content));
        $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR')));
    }

    /**
     * @test
     */
    public function it_fetches_a_rate()
    {
        $uri = 'https://api.fixer.io/latest?base=EUR';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/Fixer/latest.json');

        $provider = new Fixer($this->getHttpAdapterMock($uri, $content));
        $rate = $provider->get(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/CHF')));

        $this->assertEquals('1.0933', $rate->getValue());
        $this->assertEquals(new \DateTime('2016-08-26'), $rate->getDate());
    }

    /**
     * @test
     */
    public function it_fetches_a_historical_rate()
    {
        $uri = 'https://api.fixer.io/2000-01-03?base=USD';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Provider/Fixer/historical.json');
        $date = new \DateTime('2000-01-03');

        $provider = new Fixer($this->getHttpAdapterMock($uri, $content));
        $rate = $provider->get(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('USD/AUD'), $date));

        $this->assertEquals('1.5209', $rate->getValue());
        $this->assertEquals($date, $rate->getDate());
    }
}
