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
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\Model\CurrencyPairInterface;

class YahooFinanceSpec extends ObjectBehavior
{
    function it_is_initializable(ClientInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Provider\YahooFinance');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_sets_bid_and_date_of_one_pair(
        ClientInterface $client,
        RequestInterface $request,
        Response $response,
        CurrencyPairInterface $pair
    ) {
        $uri = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $responseContent = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/success_one.json');
        $jsonArray = json_decode($responseContent, true);

        $client->get($uri)->willReturn($request);
        $request->send()->willReturn($response);
        $response->json()->willReturn($jsonArray);

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('USD');
        $pair->setRate('1.3758')->shouldBeCalled();
        $pair->setDate(new \DateTime('2014-05-10 07:23:00'))->shouldBeCalled();

        $this->beConstructedWith($client);
        $this->quote(array($pair));
    }

    function it_sets_bid_and_date_of_three_pairs(
        ClientInterface $client,
        RequestInterface $request,
        Response $response,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    ) {
        $uri = 'https://query.yahooapis.com/v1/public/yql?q=select+%2A+from+yahoo.finance.xchange+where+pair+in+%28%22EURUSD%22%2C%22USDGBP%22%2C%22GBPEUR%22%29&env=store://datatables.org/alltableswithkeys&format=json';
        $responseContent = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/success_three.json');
        $jsonArray = json_decode($responseContent, true);

        $client->get($uri)->willReturn($request);
        $request->send()->willReturn($response);
        $response->json()->willReturn($jsonArray);

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
        ClientInterface $client,
        RequestInterface $request,
        Response $response,
        CurrencyPairInterface $pair
    ) {
        $responseContent = file_get_contents(__DIR__ . '/../../Fixtures/Provider/YahooFinance/unsupported.json');
        $jsonArray = json_decode($responseContent, true);

        $client->get(Argument::any())->willReturn($request);
        $request->send()->willReturn($response);
        $response->json()->willReturn($jsonArray);

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('XXX');

        $this->beConstructedWith($client);

        $this
            ->shouldThrow(new UnsupportedCurrencyPairException($pair->getWrappedObject()))
            ->duringQuote(array($pair))
        ;
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
}
