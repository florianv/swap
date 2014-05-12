<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests;

use Guzzle\Http\Client;
use Swap\Provider\OpenExchangeRates;
use Swap\Provider\YahooFinance;
use Swap\Provider\GoogleFinance;
use Swap\Model\CurrencyPair;
use Swap\Swap;

class SwapTest extends \PHPUnit_Framework_TestCase
{
    public function testQuoteOnePairWithYahooProvider()
    {
        $client = new Client();
        $provider = new YahooFinance($client);
        $swap = new Swap();
        $swap->addProvider($provider);

        $pair = new CurrencyPair('EUR', 'USD');

        $swap->quote($pair);

        $this->assertTrue($pair->getRate() > 0);
        $this->assertTrue($pair->getDate() <= new \DateTime());
    }

    public function testQuoteThreePairsWithYahooProvider()
    {
        $client = new Client();
        $provider = new YahooFinance($client);
        $swap = new Swap();
        $swap->addProvider($provider);

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

    public function testQuoteOnePairWithGoogleProvider()
    {
        $client = new Client();
        $provider = new GoogleFinance($client);
        $swap = new Swap();
        $swap->addProvider($provider);

        $pair = new CurrencyPair('EUR', 'USD');

        $swap->quote($pair);

        $this->assertTrue($pair->getRate() > 0);
        $this->assertTrue($pair->getDate() <= new \DateTime());
    }

    public function testQuoteThreePairsWithGoogleProvider()
    {
        $client = new Client();
        $provider = new GoogleFinance($client);
        $swap = new Swap();
        $swap->addProvider($provider);

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

    public function testQuoteThreePairsWithFailingProvider()
    {
        $client = new Client();
        $yahoo = new YahooFinance($client);
        $openExchange = new OpenExchangeRates($client, 'hihihi', true);

        $swap = new Swap();
        $swap->addProvider($openExchange);
        $swap->addProvider($yahoo);

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
}
