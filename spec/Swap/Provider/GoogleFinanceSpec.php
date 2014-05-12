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
use Guzzle\Http\Exception\MultiTransferException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swap\Exception\QuotationException;
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\Model\CurrencyPairInterface;

class GoogleFinanceSpec extends ObjectBehavior
{
    function it_is_initializable(ClientInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Provider\GoogleFinance');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_sets_bid_and_date_of_one_currency_pair(
        ClientInterface $client,
        RequestInterface $request,
        Response $response,
        CurrencyPairInterface $pair
    ) {
        $uri = 'http://google.com/finance/converter?a=1&from=USD&to=GBP';
        $html = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_usd_gbp.html');

        $client->get($uri)->willReturn($request);
        $client->send(array($request))->willReturn(array($response));
        $response->getBody(true)->willReturn($html);

        $pair->getBaseCurrency()->willReturn('USD');
        $pair->getQuoteCurrency()->willReturn('GBP');
        $pair->setRate('0.5937')->shouldBeCalled();
        $pair->setDate(Argument::any())->shouldBeCalled();

        $this->beConstructedWith($client);
        $this->quote(array($pair));
    }

    function it_sets_bid_and_date_of_three_currency_pairs(
        ClientInterface $client,
        RequestInterface $requestOne,
        Response $responseOne,
        RequestInterface $requestTwo,
        Response $responseTwo,
        RequestInterface $requestThree,
        Response $responseThree,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    ) {
        $uriOne = 'http://google.com/finance/converter?a=1&from=CHF&to=COP';
        $htmlOne = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_chf_cop.html');
        $client->get($uriOne)->willReturn($requestOne);
        $responseOne->getBody(true)->willReturn($htmlOne);

        $pairOne->getBaseCurrency()->willReturn('CHF');
        $pairOne->getQuoteCurrency()->willReturn('COP');
        $pairOne->setRate('2146.0437')->shouldBeCalled();
        $pairOne->setDate(Argument::any())->shouldBeCalled();

        $uriTwo = 'http://google.com/finance/converter?a=1&from=EUR&to=USD';
        $htmlTwo = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_eur_usd.html');
        $client->get($uriTwo)->willReturn($requestTwo);
        $responseTwo->getBody(true)->willReturn($htmlTwo);

        $pairTwo->getBaseCurrency()->willReturn('EUR');
        $pairTwo->getQuoteCurrency()->willReturn('USD');
        $pairTwo->setRate('1.3746')->shouldBeCalled();
        $pairTwo->setDate(Argument::any())->shouldBeCalled();

        $uriThree = 'http://google.com/finance/converter?a=1&from=USD&to=GBP';
        $htmlThree = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/valid_usd_gbp.html');
        $client->get($uriThree)->willReturn($requestThree);
        $responseThree->getBody(true)->willReturn($htmlThree);

        $pairThree->getBaseCurrency()->willReturn('USD');
        $pairThree->getQuoteCurrency()->willReturn('GBP');
        $pairThree->setRate('0.5937')->shouldBeCalled();
        $pairThree->setDate(Argument::any())->shouldBeCalled();

        $client->send(array($requestOne, $requestTwo, $requestThree))->willReturn(array($responseOne, $responseTwo, $responseThree));
        $this->beConstructedWith($client);

        $this->quote(array($pairOne, $pairTwo, $pairThree));
    }

    function it_throws_unsupported_currency_pair_when_rate_cannot_be_parsed(
        ClientInterface $client,
        RequestInterface $request,
        Response $response,
        CurrencyPairInterface $pair
    ) {
        $html = file_get_contents(__DIR__ . '/../../Fixtures/Provider/GoogleFinance/invalid_convert_eur_all.html');
        $response->getBody(true)->willReturn($html);

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('ALL');

        $client->get(Argument::any())->willReturn($request);
        $client->send(array($request))->willReturn(array($response));

        $this->beConstructedWith($client);

        $this
            ->shouldThrow(new UnsupportedCurrencyPairException($pair->getWrappedObject()))
            ->duringQuote(array($pair))
        ;
    }

    function it_throws_quotation_exception_on_transfert_exception(
        ClientInterface $client,
        RequestInterface $request,
        Response $response,
        CurrencyPairInterface $pair
    ) {
        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('ALL');

        $response->getStatusCode()->willReturn(500);
        $request->getResponse()->willReturn($response);

        $exception = new MultiTransferException();
        $exception->addFailedRequest($request->getWrappedObject());

        $client->get(Argument::any())->willReturn($request);
        $client->send(array($request))->willThrow($exception);

        $this->beConstructedWith($client);

        $this
            ->shouldThrow(new QuotationException('The request failed with a "500" status code.'))
            ->duringQuote(array($pair))
        ;
    }

    function it_throws_quotation_exception_on_exception(
        ClientInterface $client,
        RequestInterface $request,
        CurrencyPairInterface $pair
    ) {
        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('ALL');

        $exception = new \Exception('error');

        $client->get(Argument::any())->willReturn($request);
        $client->send(array($request))->willThrow($exception);

        $this->beConstructedWith($client);

        $this
            ->shouldThrow(new QuotationException('The request failed with message: "error".'))
            ->duringQuote(array($pair))
        ;
    }
}
