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

class GoogleFinanceSpec extends ObjectBehavior
{
    function it_is_initializable(AdapterInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Provider\GoogleFinance');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_sets_bid_and_date_of_one_currency_pair(
        AdapterInterface $client,
        CurrencyPairInterface $pair
    )
    {
        $uri = 'http://google.com/finance/converter?a=1&from=USD&to=GBP';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_usd_gbp.html');
        $client->getAll(array($uri))->willReturn(array($body));

        $pair->getBaseCurrency()->willReturn('USD');
        $pair->getQuoteCurrency()->willReturn('GBP');
        $pair->setRate('0.5937')->shouldBeCalled();
        $pair->setDate(Argument::any())->shouldBeCalled();

        $this->beConstructedWith($client);
        $this->quote(array($pair));
    }

    function it_sets_bid_and_date_of_three_currency_pairs(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    )
    {
        $uriOne = 'http://google.com/finance/converter?a=1&from=CHF&to=COP';
        $bodyOne = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_chf_cop.html');

        $pairOne->getBaseCurrency()->willReturn('CHF');
        $pairOne->getQuoteCurrency()->willReturn('COP');
        $pairOne->setRate('2146.0437')->shouldBeCalled();
        $pairOne->setDate(Argument::any())->shouldBeCalled();

        $uriTwo = 'http://google.com/finance/converter?a=1&from=EUR&to=USD';
        $bodyTwo = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_eur_usd.html');

        $pairTwo->getBaseCurrency()->willReturn('EUR');
        $pairTwo->getQuoteCurrency()->willReturn('USD');
        $pairTwo->setRate('1.3746')->shouldBeCalled();
        $pairTwo->setDate(Argument::any())->shouldBeCalled();

        $uriThree = 'http://google.com/finance/converter?a=1&from=USD&to=GBP';
        $bodyThree = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_usd_gbp.html');

        $pairThree->getBaseCurrency()->willReturn('USD');
        $pairThree->getQuoteCurrency()->willReturn('GBP');
        $pairThree->setRate('0.5937')->shouldBeCalled();
        $pairThree->setDate(Argument::any())->shouldBeCalled();

        $client->getAll(array($uriOne, $uriTwo, $uriThree))->willReturn(array($bodyOne, $bodyTwo, $bodyThree));
        $this->beConstructedWith($client);

        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }

    function it_throws_unsupported_currency_pair_when_rate_cannot_be_parsed(
        AdapterInterface $client,
        CurrencyPairInterface $pair
    )
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/invalid_convert_eur_all.html');

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('ALL');

        $client->getAll(Argument::any())->willReturn(array($body));

        $this->beConstructedWith($client);

        $this
            ->shouldThrow(new UnsupportedCurrencyPairException($pair->getWrappedObject()))
            ->duringQuote(array($pair))
        ;
    }
}
