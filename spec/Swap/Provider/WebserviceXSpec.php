<?php

namespace spec\Swap\Provider;

use Swap\AdapterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swap\Model\CurrencyPairInterface;

class WebserviceXSpec extends ObjectBehavior
{
    function it_is_initializable(AdapterInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Provider\WebserviceX');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_sets_the_bid_and_date_of_one_pair_when_successful(
        AdapterInterface $client,
        CurrencyPairInterface $pair
    )
    {
        $uri = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=USD';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_1.xml');
        $client->getAll(array($uri))->willReturn(array($body));

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('USD');
        $pair->setRate('1.3608')->shouldBeCalled();
        $pair->setDate(Argument::any())->shouldBeCalled();

        $this->beConstructedWith($client);
        $this->quote(array($pair));
    }

    function it_sets_the_bid_and_date_of_three_pairs_when_successful(
        AdapterInterface $client,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    )
    {
        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('USD');
        $pairOne->setRate('1.3608')->shouldBeCalled();
        $pairOne->setDate(Argument::any())->shouldBeCalled();

        $uriOne = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=USD';
        $bodyOne = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_1.xml');

        $pairTwo->getBaseCurrency()->willReturn('EUR');
        $pairTwo->getQuoteCurrency()->willReturn('BOB');
        $pairTwo->setRate('2.5000')->shouldBeCalled();
        $pairTwo->setDate(Argument::any())->shouldBeCalled();

        $uriTwo = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=BOB';
        $bodyTwo = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_2.xml');

        $pairThree->getBaseCurrency()->willReturn('USD');
        $pairThree->getQuoteCurrency()->willReturn('BOB');
        $pairThree->setRate('3.5000')->shouldBeCalled();
        $pairThree->setDate(Argument::any())->shouldBeCalled();

        $uriThree = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=USD&ToCurrency=BOB';
        $bodyThree = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_3.xml');

        $client->getAll(array($uriOne, $uriTwo, $uriThree))->willReturn(array($bodyOne, $bodyTwo, $bodyThree));

        $this->beConstructedWith($client);
        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }
}
