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

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swap\Exception\QuotationException;
use Swap\Model\CurrencyPairInterface;

class XigniteSpec extends ObjectBehavior
{
    function it_is_initializable(ClientInterface $client)
    {
        $this->beConstructedWith($client, 'secret');
        $this->shouldHaveType('Swap\Provider\Xignite');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_sets_the_bid_and_date_of_one_pair_when_successful(
        ClientInterface $client,
        Response $response,
        RequestInterface $request,
        CurrencyPairInterface $pair
    ) {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=GBPAWG&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=token';
        $jsonArray = json_decode(file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/success_one.json'), true);

        $response->json()->willReturn($jsonArray);
        $request->send()->willReturn($response);
        $client->get(Argument::exact($uri))->willReturn($request);

        $pair->getBaseCurrency()->willReturn('GBP');
        $pair->getQuoteCurrency()->willReturn('AWG');
        $pair->setDate(Argument::exact(new \DateTime('2014-05-11 21:22:00', new \DateTimeZone('UTC'))))->shouldBeCalled();
        $pair->setRate(Argument::exact('2.982308'))->shouldBeCalled();
        $pair->getRate()->willReturn('2.982308');

        $this->beConstructedWith($client, 'token');

        $this->quote(array($pair));
    }

    function it_sets_the_bid_and_date_of_three_pairs_when_successful(
        ClientInterface $client,
        Response $response,
        RequestInterface $request,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    ) {
        $uri = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=EURUSD,AUDUSD,AEDAOA&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=secret';
        $jsonArray = json_decode(file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/success_three.json'), true);

        $response->json()->willReturn($jsonArray);
        $request->send()->willReturn($response);
        $client->get(Argument::exact($uri))->willReturn($request);

        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('USD');
        $pairOne->setDate(Argument::exact(new \DateTime('2014-05-11 20:54:10', new \DateTimeZone('UTC'))))->shouldBeCalled();
        $pairOne->setRate(Argument::exact('1.3758'))->shouldBeCalled();
        $pairOne->getRate()->willReturn('1.3758');

        $pairTwo->getBaseCurrency()->willReturn('AUD');
        $pairTwo->getQuoteCurrency()->willReturn('USD');
        $pairTwo->setDate(Argument::exact(new \DateTime('2014-10-10 11:23:10', new \DateTimeZone('UTC'))))->shouldBeCalled();
        $pairTwo->setRate(Argument::exact('0.9355'))->shouldBeCalled();
        $pairTwo->getRate()->willReturn('0.9355');

        $pairThree->getBaseCurrency()->willReturn('AED');
        $pairThree->getQuoteCurrency()->willReturn('AOA');
        $pairThree->setDate(Argument::exact(new \DateTime('2014-07-10 21:20:00', new \DateTimeZone('UTC'))))->shouldBeCalled();
        $pairThree->setRate(Argument::exact('26.55778'))->shouldBeCalled();
        $pairThree->getRate()->willReturn('26.55778');

        $this->beConstructedWith($client, 'secret');

        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }

    function it_throws_a_quotation_exception_on_bad_response(
        ClientInterface $client,
        Response $response,
        RequestInterface $request,
        CurrencyPairInterface $pair
    ) {
        $response->getStatusCode()->willReturn(500);

        $exception = new BadResponseException();
        $exception->setRequest($request->getWrappedObject());
        $exception->setResponse($response->getWrappedObject());

        $request->send()->willThrow($exception);
        $client->get(Argument::any())->willReturn($request);

        $pair->getBaseCurrency()->willReturn('AUD');
        $pair->getQuoteCurrency()->willReturn('USD');

        $this->beConstructedWith($client, 'secret');

        $this
            ->shouldThrow(new QuotationException('The request failed with a "500" status code.'))
            ->duringQuote(array($pair))
        ;
    }

    function it_throws_a_quotation_exception_on_exception(
        ClientInterface $client,
        RequestInterface $request,
        CurrencyPairInterface $pair
    ) {
        $exception = new \Exception('error');

        $request->send()->willThrow($exception);
        $client->get(Argument::any())->willReturn($request);

        $this->beConstructedWith($client, 'secret');

        $this
            ->shouldThrow(new QuotationException('The request failed with message: "error".'))
            ->duringQuote(array($pair))
        ;
    }

    function it_throws_a_quotation_exception_on_error_outcome(
        ClientInterface $client,
        Response $response,
        RequestInterface $request,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo
    ) {
        $jsonArray = json_decode(file_get_contents(__DIR__ . '/../../Fixtures/Provider/Xignite/error_success.json'), true);

        $response->json()->willReturn($jsonArray);
        $request->send()->willReturn($response);
        $client->get(Argument::any())->willReturn($request);

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
