<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Service;

use Swap\ExchangeRateQuery;
use Swap\HistoricalExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\Service\CentralBankOfCzechRepublic;

/**
 * @author Petr Kramar <petr.kramar@perlur.cz>
 */
class CentralBankOfCzechRepublicTest extends ServiceTestCase
{
    /**
     * @var string URL of CNB exchange rates
     */
    protected static $url;

    /**
     * @var string content of CNB exchange rates
     */
    protected static $content;

    /**
     * Set up variables before TestCase is being initialized.
     */
    public static function setUpBeforeClass()
    {
        $fixture_path = __DIR__.'/../../Fixtures/Provider/CentralBankOfCzechRepublic/cnb_today.txt';

        self::$url = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';
        self::$content = file_get_contents($fixture_path);
    }

    /**
     * Clean variables after TestCase finish.
     */
    public static function tearDownAfterClass()
    {
        self::$url = null;
        self::$content = null;
    }

    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        $provider = new CentralBankOfCzechRepublic($this->getMock('Http\Client\HttpClient'));

        $this->assertFalse($provider->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('CZK/EUR'))));
        $this->assertFalse($provider->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('XXX/TRY'))));
        $this->assertFalse($provider->supportQuery(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('XXX/TRY'), new \DateTime())));
    }

    /**
     * @test
     */
    public function itFetchesAEurRate()
    {
        $provider = $this->createProvider();
        $rate = $provider->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/CZK')));

        $this->assertSame('27.035', $rate->getValue());
        $this->assertEquals(new \DateTime('2016-04-05'), $rate->getDate());
    }

    /**
     * @test
     */
    public function itFetchesAPhpRate()
    {
        $rate = $this->createProvider()->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('PHP/CZK')));
        $this->assertSame('0.51384', $rate->getValue());
    }

    /**
     * @test
     */
    public function itFetchesAIdrRate()
    {
        $rate = $this->createProvider()->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('IDR/CZK')));
        $this->assertSame('0.001798', $rate->getValue());
    }

    /**
     * Create bank provider.
     *
     * @return CentralBankOfCzechRepublic
     */
    protected function createProvider()
    {
        return new CentralBankOfCzechRepublic($this->getHttpAdapterMock(self::$url, self::$content));
    }
}
