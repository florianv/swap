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
use Swap\Exception\UnsupportedBaseCurrencyException;
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\Model\CurrencyPairInterface;

class EuropeanCentralBankSpec extends ObjectBehavior
{
    function it_is_initializable(AdapterInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Provider\EuropeanCentralBank');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_throws_an_unsupported_base_currency_exception_when_base_is_not_euro(
        AdapterInterface $client,
        CurrencyPairInterface $pair
    )
    {
        $pair->getBaseCurrency()->willReturn('USD');

        $this->beConstructedWith($client);

        $this
            ->shouldThrow(new UnsupportedBaseCurrencyException('USD'))
            ->duringQuote(array($pair))
        ;
    }

    function it_sets_the_bid_and_date_of_three_pairs(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    )
    {
        $uri = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/EuropeanCentralBank/daily.xml');
        $client->get($uri)->willReturn($body);

        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('BGN');
        $pairOne->setRate('1.9558')->shouldBeCalled();
        $pairOne->getRate()->willReturn('1.9558');
        $pairOne->setDate(new \DateTime('2014-05-09'))->shouldBeCalled();

        $pairTwo->getBaseCurrency()->willReturn('EUR');
        $pairTwo->getQuoteCurrency()->willReturn('KRW');
        $pairTwo->setRate('1413.41')->shouldBeCalled();
        $pairTwo->getRate()->willReturn('1413.41');
        $pairTwo->setDate(new \DateTime('2014-05-09'))->shouldBeCalled();

        $pairThree->getBaseCurrency()->willReturn('EUR');
        $pairThree->getQuoteCurrency()->willReturn('RUB');
        $pairThree->setRate('48.5270')->shouldBeCalled();
        $pairThree->getRate()->willReturn('48.5270');
        $pairThree->setDate(new \DateTime('2014-05-09'))->shouldBeCalled();

        $this->beConstructedWith($client);
        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }

    function it_throws_an_unsupported_currency_pair_if_the_pair_is_not_quoted(
        AdapterInterface $client,
        CurrencyPairInterface $pair
    )
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/EuropeanCentralBank/daily.xml');
        $client->get(Argument::any())->willReturn($body);

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('XXX');
        $pair->getRate()->willReturn(null);

        $this->beConstructedWith($client);

        $this
            ->shouldThrow(new UnsupportedCurrencyPairException($pair->getWrappedObject()))
            ->duringQuote(array($pair))
        ;
    }
}
