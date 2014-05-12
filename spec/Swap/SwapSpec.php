<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Swap;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swap\Model\CurrencyPairInterface;
use Swap\ProviderInterface;

class SwapSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Swap\Swap');
        $this->shouldImplement('Swap\SwapInterface');
    }

    function it_adds_a_provider(ProviderInterface $provider)
    {
        $this->addProvider($provider);
        $this->hasProvider($provider)->shouldReturn(true);
    }

    function it_can_be_constructed_with_multiple_providers(
        ProviderInterface $providerOne,
        ProviderInterface $providerTwo
    ) {
        $this->beConstructedWith(array($providerOne, $providerTwo));
        $this->hasProvider($providerOne)->shouldReturn(true);
        $this->hasProvider($providerTwo)->shouldReturn(true);
    }

    function it_throws_a_runtime_exception_if_no_providers(CurrencyPairInterface $pair)
    {
        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('USD');

        $this
            ->shouldThrow(new \RuntimeException('No providers have been added.'))
            ->duringQuote($pair)
        ;
    }

    function it_accepts_a_single_pair(
        ProviderInterface $provider,
        CurrencyPairInterface $pair
    ) {
        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('USD');

        $provider->quote(array($pair))->shouldBeCalled();

        $this->addProvider($provider);
        $this->quote($pair);
    }

    function it_doesnt_call_providers_if_pairs_empty(ProviderInterface $provider)
    {
        $provider->quote(Argument::any())->shouldNotBeCalled();

        $this->addProvider($provider);
        $this->quote(array());
    }

    function it_sets_the_rate_and_date_if_base_and_quote_currencies_are_identical(
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo
    ) {
        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('EUR');
        $pairOne->setRate('1')->shouldBeCalled();
        $pairOne->setDate(Argument::any())->shouldBeCalled();

        $pairTwo->getBaseCurrency()->willReturn('USD');
        $pairTwo->getQuoteCurrency()->willReturn('USD');
        $pairTwo->setRate('1')->shouldBeCalled();
        $pairTwo->setDate(Argument::any())->shouldBeCalled();

        $this->quote(array($pairOne, $pairTwo));
    }

    function it_forwards_failed_pair_to_next_provider(
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree,
        ProviderInterface $providerOne,
        ProviderInterface $providerTwo,
        ProviderInterface $providerThree
    ) {
        $date = new \DateTime();

        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('USD');
        $pairOne->getRate()->willReturn(null);
        $pairOne->setRate('11')->shouldBeCalled();
        $pairOne->setDate($date)->shouldBeCalled();

        $pairTwo->getBaseCurrency()->willReturn('USD');
        $pairTwo->getQuoteCurrency()->willReturn('EUR');
        $pairTwo->getRate()->willReturn(null);
        $pairTwo->setRate('11')->shouldBeCalled();
        $pairTwo->setDate($date)->shouldBeCalled();

        $pairThree->getBaseCurrency()->willReturn('GBP');
        $pairThree->getQuoteCurrency()->willReturn('USD');
        $pairThree->getRate()->willReturn(null);
        $pairThree->setRate('11')->shouldBeCalled();
        $pairThree->setDate($date)->shouldBeCalled();

        $providerOne->quote(array($pairOne, $pairTwo, $pairThree))->will(function() use ($pairOne, $date) {
            $pairOne->getRate()->willReturn('11');
            $pairOne->getWrappedObject()->setRate('11');
            $pairOne->getWrappedObject()->setDate($date);
            throw new \Exception();
        });

        $providerTwo->quote(array($pairTwo, $pairThree))->will(function() use ($pairTwo, $date) {
            $pairTwo->getRate()->willReturn('11');
            $pairTwo->getWrappedObject()->setRate('11');
            $pairTwo->getWrappedObject()->setDate($date);
            throw new \Exception();
        });

        $providerThree->quote(array($pairThree))->will(function() use ($pairThree, $date) {
            $pairThree->getRate()->willReturn('11');
            $pairThree->getWrappedObject()->setRate('11');
            $pairThree->getWrappedObject()->setDate($date);
        });

        $this->beConstructedWith(array($providerOne, $providerTwo, $providerThree));
        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }

    function it_throws_the_exception_of_the_last_provider(
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree,
        ProviderInterface $providerOne,
        ProviderInterface $providerTwo,
        ProviderInterface $providerThree
    ) {
        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('USD');
        $pairOne->getRate()->willReturn(null);

        $pairTwo->getBaseCurrency()->willReturn('USD');
        $pairTwo->getQuoteCurrency()->willReturn('EUR');
        $pairTwo->getRate()->willReturn(null);

        $pairThree->getBaseCurrency()->willReturn('GBP');
        $pairThree->getQuoteCurrency()->willReturn('USD');
        $pairThree->getRate()->willReturn(null);

        $providerOne->quote(array($pairOne, $pairTwo, $pairThree))->will(function() use ($pairOne) {
            $pairOne->getRate()->willReturn('11');
            throw new \Exception();
        });

        $providerTwo->quote(array($pairTwo, $pairThree))->will(function() use ($pairTwo) {
            $pairTwo->getRate()->willReturn('11');
            throw new \Exception();
        });

        $providerThree->quote(array($pairThree))->will(function() {
            throw new \Exception('oops');
        });

        $this->beConstructedWith(array($providerOne, $providerTwo, $providerThree));

        $this
            ->shouldThrow(new \Exception('oops'))
            ->duringQuote(array($pairOne, $pairTwo, $pairThree))
        ;
    }
}
