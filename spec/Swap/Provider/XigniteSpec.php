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
use Swap\Exception\QuotationException;
use Swap\Model\CurrencyPairInterface;

class XigniteSpec extends ObjectBehavior
{
    function it_is_initializable(AdapterInterface $client)
    {
        $this->beConstructedWith($client, 'secret');
        $this->shouldHaveType('Swap\Provider\Xignite');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_sets_the_bid_and_date_of_one_pair_when_successful(
        AdapterInterface $client,
        CurrencyPairInterface $pair
    )
    {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=GBPAWG&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=token';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/success_one.json');
        $client->get($uri)->willReturn($body);

        $pair->getBaseCurrency()->willReturn('GBP');
        $pair->getQuoteCurrency()->willReturn('AWG');
        $pair->setDate(new \DateTime('2014-05-11 21:22:00', new \DateTimeZone('UTC')))->shouldBeCalled();
        $pair->setRate('2.982308')->shouldBeCalled();
        $pair->getRate()->willReturn('2.982308');

        $this->beConstructedWith($client, 'token');
        $this->quote(array($pair));
    }

    function it_sets_the_bid_and_date_of_three_pairs_when_successful(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    )
    {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=EURUSD,AUDUSD,AEDAOA&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=secret';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/success_three.json');
        $client->get($uri)->willReturn($body);

        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('USD');
        $pairOne->setDate(new \DateTime('2014-05-11 20:54:10', new \DateTimeZone('UTC')))->shouldBeCalled();
        $pairOne->setRate('1.3758')->shouldBeCalled();
        $pairOne->getRate()->willReturn('1.3758');

        $pairTwo->getBaseCurrency()->willReturn('AUD');
        $pairTwo->getQuoteCurrency()->willReturn('USD');
        $pairTwo->setDate(new \DateTime('2014-10-10 11:23:10', new \DateTimeZone('UTC')))->shouldBeCalled();
        $pairTwo->setRate('0.9355')->shouldBeCalled();
        $pairTwo->getRate()->willReturn('0.9355');

        $pairThree->getBaseCurrency()->willReturn('AED');
        $pairThree->getQuoteCurrency()->willReturn('AOA');
        $pairThree->setDate(new \DateTime('2014-07-10 21:20:00', new \DateTimeZone('UTC')))->shouldBeCalled();
        $pairThree->setRate('26.55778')->shouldBeCalled();
        $pairThree->getRate()->willReturn('26.55778');

        $this->beConstructedWith($client, 'secret');
        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }

    function it_throws_a_quotation_exception_on_error_outcome(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo
    )
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/error_success.json');
        $client->get(Argument::any())->willReturn($body);

        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('USD');
        $pairOne->setRate('1.37562')->shouldBeCalled();
        $pairOne->setDate(new \DateTime('2014-05-11 21:18:50', new \DateTimeZone('UTC')))->shouldBeCalled();

        $pairTwo->getBaseCurrency()->willReturn('EUR');
        $pairTwo->getQuoteCurrency()->willReturn('XXX');

        $this->beConstructedWith($client, 'secret');

        $this
            ->shouldThrow(new QuotationException('Error message'))
            ->duringQuote(array($pairOne, $pairTwo))
        ;
    }
}
