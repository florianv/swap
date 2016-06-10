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

use Swap\Model\CurrencyPair;
use Swap\Provider\CentralBankOfCzechRepublicProvider;

/**
 * @author Petr Kramar <petr.kramar@perlur.cz>
 */
class CentralBankOfCzechRepublicProviderTest extends AbstractProviderTestCase
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
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function itThrowsAnExceptionWhenQuotesIsNotCzk()
    {
        $provider = $this->createProvider();
        $provider->fetchRate(new CurrencyPair('CZK', 'EUR'));
    }

    /**
     * @test
     * @expectedException \Swap\Exception\UnsupportedCurrencyPairException
     */
    public function itThrowsAnExceptionWhenThePairIsNotSupported()
    {
        $provider = $this->createProvider();
        $provider->fetchRate(new CurrencyPair('XXX', 'TRY'));
    }

    /**
     * @test
     */
    public function itFetchesAEurRate()
    {
        $provider = $this->createProvider();
        $rate = $provider->fetchRate(new CurrencyPair('EUR', 'CZK'));

        $this->assertSame('27.035', $rate->getValue());
        $this->assertEquals(new \DateTime('2016-04-05'), $rate->getDate());
    }

    /**
     * @test
     */
    public function itFetchesAPhpRate()
    {
        $rate = $this->createProvider()->fetchRate(new CurrencyPair('PHP', 'CZK'));
        $this->assertSame('0.51384', $rate->getValue());
    }

    /**
     * @test
     */
    public function itFetchesAIdrRate()
    {
        $rate = $this->createProvider()->fetchRate(new CurrencyPair('IDR', 'CZK'));
        $this->assertSame('0.001798', $rate->getValue());
    }

    /**
     * Create bank provider.
     *
     * @return CentralBankOfCzechRepublicProvider
     */
    protected function createProvider()
    {
        return new CentralBankOfCzechRepublicProvider($this->getHttpAdapterMock(self::$url, self::$content));
    }
}
