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

use Swap\Provider\EuropeanCentralBank;
use Swap\Provider\WebserviceX;
use Swap\Provider\YahooFinance;
use Swap\Provider\GoogleFinance;
use Swap\Model\CurrencyPair;
use Swap\Swap;

abstract class AbstractIntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * The client or adapter to test.
     *
     * @var \Guzzle\Http\ClientInterface|\Swap\AdapterInterface
     */
    protected $adapter;

    /**
     * @test
     */
    function it_quotes_one_pair_with_yahoo()
    {
        $swap = new Swap();
        $swap->addProvider(new YahooFinance($this->adapter));

        $pair = new CurrencyPair('EUR', 'USD');

        $swap->quote($pair);

        $this->assertTrue($pair->getRate() > 0);
        $this->assertTrue($pair->getDate() <= new \DateTime());
    }

    /**
     * @test
     */
    function it_quotes_three_pairs_with_yahoo()
    {
        $swap = new Swap();
        $swap->addProvider(new YahooFinance($this->adapter));

        $eurUsd = new CurrencyPair('EUR', 'USD');
        $usdGbp = new CurrencyPair('USD', 'GBP');
        $gbpJpy = new CurrencyPair('GBP', 'JPY');

        $swap->quote(array($eurUsd, $usdGbp, $gbpJpy));

        $this->assertTrue($eurUsd->getRate() > 0);
        $this->assertTrue($eurUsd->getDate() <= new \DateTime());

        $this->assertTrue($usdGbp->getRate() > 0);
        $this->assertTrue($usdGbp->getDate() <= new \DateTime());

        $this->assertTrue($gbpJpy->getRate() > 0);
        $this->assertTrue($gbpJpy->getDate() <= new \DateTime());
    }

    /**
     * @test
     */
    function it_quotes_one_pair_with_webservicex()
    {
        $swap = new Swap();
        $swap->addProvider(new WebserviceX($this->adapter));

        $pair = new CurrencyPair('EUR', 'USD');

        $swap->quote($pair);

        $this->assertTrue($pair->getRate() > 0);
        $this->assertTrue($pair->getDate() <= new \DateTime());
    }

    /**
     * @test
     */
    function it_quotes_three_pairs_with_webservicex()
    {
        $swap = new Swap();
        $swap->addProvider(new WebserviceX($this->adapter));

        $eurUsd = new CurrencyPair('EUR', 'USD');
        $usdGbp = new CurrencyPair('USD', 'GBP');
        $gbpJpy = new CurrencyPair('GBP', 'JPY');

        $swap->quote(array($eurUsd, $usdGbp, $gbpJpy));

        $this->assertTrue($eurUsd->getRate() > 0);
        $this->assertTrue($eurUsd->getDate() <= new \DateTime());

        $this->assertTrue($usdGbp->getRate() > 0);
        $this->assertTrue($usdGbp->getDate() <= new \DateTime());

        $this->assertTrue($gbpJpy->getRate() > 0);
        $this->assertTrue($gbpJpy->getDate() <= new \DateTime());
    }

    /**
     * @test
     */
    function it_quotes_one_pair_with_google()
    {
        $swap = new Swap();
        $swap->addProvider(new GoogleFinance($this->adapter));

        $pair = new CurrencyPair('EUR', 'USD');

        $swap->quote($pair);

        $this->assertTrue($pair->getRate() > 0);
        $this->assertTrue($pair->getDate() <= new \DateTime());
    }

    /**
     * @test
     */
    function it_quotes_three_pairs_with_google()
    {
        $swap = new Swap();
        $swap->addProvider(new GoogleFinance($this->adapter));

        $eurUsd = new CurrencyPair('EUR', 'USD');
        $usdGbp = new CurrencyPair('USD', 'GBP');
        $gbpJpy = new CurrencyPair('GBP', 'JPY');

        $swap->quote(array($eurUsd, $usdGbp, $gbpJpy));

        $this->assertTrue($eurUsd->getRate() > 0);
        $this->assertTrue($eurUsd->getDate() <= new \DateTime());

        $this->assertTrue($usdGbp->getRate() > 0);
        $this->assertTrue($usdGbp->getDate() <= new \DateTime());

        $this->assertTrue($gbpJpy->getRate() > 0);
        $this->assertTrue($gbpJpy->getDate() <= new \DateTime());
    }

    /**
     * @test
     */
    function it_quotes_one_pair_with_ecb()
    {
        $swap = new Swap();
        $swap->addProvider(new EuropeanCentralBank($this->adapter));

        $pair = new CurrencyPair('EUR', 'USD');

        $swap->quote($pair);

        $this->assertTrue($pair->getRate() > 0);
        $this->assertTrue($pair->getDate() <= new \DateTime());
    }

    /**
     * @test
     */
    function it_quotes_three_pairs_with_ecb()
    {
        $swap = new Swap();
        $swap->addProvider(new EuropeanCentralBank($this->adapter));

        $eurUsd = new CurrencyPair('EUR', 'USD');
        $usdGbp = new CurrencyPair('EUR', 'GBP');
        $gbpJpy = new CurrencyPair('EUR', 'JPY');

        $swap->quote(array($eurUsd, $usdGbp, $gbpJpy));

        $this->assertTrue($eurUsd->getRate() > 0);
        $this->assertTrue($eurUsd->getDate() <= new \DateTime());

        $this->assertTrue($usdGbp->getRate() > 0);
        $this->assertTrue($usdGbp->getDate() <= new \DateTime());

        $this->assertTrue($gbpJpy->getRate() > 0);
        $this->assertTrue($gbpJpy->getDate() <= new \DateTime());
    }
}
