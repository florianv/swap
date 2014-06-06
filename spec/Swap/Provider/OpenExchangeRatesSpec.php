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

class OpenExchangeRatesSpec extends ObjectBehavior
{
    function it_is_initializable(AdapterInterface $client)
    {
        $this->beConstructedWith($client, 'id', true);
        $this->shouldHaveType('Swap\Provider\OpenExchangeRates');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_throws_an_invalid_argument_exception_if_base_is_not_usd_and_non_enterprise_mode(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo
    ) {
        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairTwo->getBaseCurrency()->willReturn('EUR');

        $this->beConstructedWith($client, 'id');

        $this
            ->shouldThrow(new UnsupportedBaseCurrencyException('EUR'))
            ->duringQuote(array($pairOne, $pairTwo))
        ;
    }

    function it_sets_bid_and_date_of_four_pairs_with_different_base_enterprise_mode(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree,
        CurrencyPairInterface $pairFour
    )
    {
        $uriOne = 'https://openexchangerates.org/api/latest.json?app_id=secret&base=EUR&symbols=AED,AFN';
        $bodyOne = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success_eur.json');

        $uriTwo = 'https://openexchangerates.org/api/latest.json?app_id=secret&base=USD&symbols=ALL,AMD';
        $bodyTwo = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success_usd.json');

        $dateOne = new \DateTime();
        $dateOne->setTimestamp(1399748450);

        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('AED');
        $pairOne->setRate('3.672947')->shouldBeCalled();
        $pairOne->setDate($dateOne)->shouldBeCalled();

        $pairTwo->getBaseCurrency()->willReturn('EUR');
        $pairTwo->getQuoteCurrency()->willReturn('AFN');
        $pairTwo->setRate('56.777675')->shouldBeCalled();
        $pairTwo->setDate($dateOne)->shouldBeCalled();

        $pairThree->getBaseCurrency()->willReturn('USD');
        $pairThree->getQuoteCurrency()->willReturn('ALL');
        $pairThree->setRate('101.669799')->shouldBeCalled();
        $pairThree->setDate($dateOne)->shouldBeCalled();

        $pairFour->getBaseCurrency()->willReturn('USD');
        $pairFour->getQuoteCurrency()->willReturn('AMD');
        $pairFour->setRate('416.085')->shouldBeCalled();
        $pairFour->setDate($dateOne)->shouldBeCalled();

        $client->getAll(array($uriOne, $uriTwo))->willReturn(array($bodyOne, $bodyTwo));

        $this->beConstructedWith($client, 'secret', true);
        $this->quote(array($pairOne, $pairTwo, $pairThree, $pairFour));
    }

    function it_sets_bid_and_date_of_three_pairs_non_enterprise(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    )
    {
        $uri = 'https://openexchangerates.org/api/latest.json?app_id=secret';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success_usd.json');

        $date = new \DateTime();
        $date->setTimestamp(1399748450);

        $pairOne->getBaseCurrency()->willReturn('USD');
        $pairOne->getQuoteCurrency()->willReturn('EUR');
        $pairOne->setDate($date)->shouldBeCalled();
        $pairOne->setRate('0.726804')->shouldBeCalled();

        $pairTwo->getBaseCurrency()->willReturn('USD');
        $pairTwo->getQuoteCurrency()->willReturn('GBP');
        $pairTwo->setDate($date)->shouldBeCalled();
        $pairTwo->setRate('0.593408')->shouldBeCalled();

        $pairThree->getBaseCurrency()->willReturn('USD');
        $pairThree->getQuoteCurrency()->willReturn('HKD');
        $pairThree->setDate($date)->shouldBeCalled();
        $pairThree->setRate('7.751676')->shouldBeCalled();

        $client->getAll(array($uri))->willReturn(array($body));

        $this->beConstructedWith($client, 'secret');
        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }

    function it_throws_an_unsupported_currency_pair_exception_when_rate_not_present(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo
    )
    {
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/OpenExchangeRates/success_usd.json');
        $date = new \DateTime();
        $date->setTimestamp(1399748450);

        $pairOne->getBaseCurrency()->willReturn('USD');
        $pairOne->getQuoteCurrency()->willReturn('EUR');
        $pairOne->setDate($date)->shouldBeCalled();
        $pairOne->setRate('0.726804')->shouldBeCalled();

        $pairTwo->getBaseCurrency()->willReturn('USD');
        $pairTwo->getQuoteCurrency()->willReturn('XXX');

        $client->getAll(Argument::any())->willReturn(array($body));

        $this->beConstructedWith($client, 'secret');

        $this
            ->shouldThrow(new UnsupportedCurrencyPairException($pairTwo->getWrappedObject()))
            ->duringQuote(array($pairOne, $pairTwo))
        ;
    }
}
