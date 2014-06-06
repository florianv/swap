<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Swap\Provider;

use Swap\AdapterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\Model\CurrencyPairInterface;

class YahooFinanceSpec extends ObjectBehavior
{
    function it_is_initializable(AdapterInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Provider\YahooFinance');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_sets_bid_and_date_of_one_pair(
        AdapterInterface $client,
        CurrencyPairInterface $pair
    )
    {
        $uri = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/success_one.json');
        $client->get($uri)->willReturn($body);

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('USD');
        $pair->setRate('1.3758')->shouldBeCalled();
        $pair->setDate(new \DateTime('2014-05-10 07:23:00'))->shouldBeCalled();

        $this->beConstructedWith($client);
        $this->quote(array($pair));
    }

    function it_sets_bid_and_date_of_three_pairs(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    )
    {
        $uri = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%2C%22USDGBP%22%2C%22GBPEUR%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/success_three.json');
        $client->get($uri)->willReturn($body);

        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('USD');
        $pairOne->setRate('1.3758')->shouldBeCalled();
        $pairOne->setDate(new \DateTime('2014-05-10 07:23:00'))->shouldBeCalled();

        $pairTwo->getBaseCurrency()->willReturn('USD');
        $pairTwo->getQuoteCurrency()->willReturn('GBP');
        $pairTwo->setRate('0.5935')->shouldBeCalled();
        $pairTwo->setDate(new \DateTime('2014-05-10 20:23:00'))->shouldBeCalled();

        $pairThree->getBaseCurrency()->willReturn('GBP');
        $pairThree->getQuoteCurrency()->willReturn('EUR');
        $pairThree->setRate('1.2248')->shouldBeCalled();
        $pairThree->setDate(new \DateTime('2014-05-12 07:23:00'))->shouldBeCalled();

        $this->beConstructedWith($client);
        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }

    function it_throws_exception_when_currency_pair_is_not_supported(
        AdapterInterface $client,
        CurrencyPairInterface $pair
    )
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/unsupported.json');
        $client->get(Argument::any())->willReturn($body);

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('XXX');

        $this->beConstructedWith($client);

        $this
            ->shouldThrow(new UnsupportedCurrencyPairException($pair->getWrappedObject()))
            ->duringQuote(array($pair))
        ;
    }
}
