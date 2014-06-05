<?php

namespace spec\Swap\Provider;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\MultiTransferException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swap\Exception\QuotationException;
use Swap\Model\CurrencyPairInterface;

class WebserviceXSpec extends ObjectBehavior
{
    function it_is_initializable(ClientInterface $client)
    {
        $this->beConstructedWith($client);
        $this->shouldHaveType('Swap\Provider\WebserviceX');
        $this->shouldImplement('Swap\ProviderInterface');
    }

    function it_sets_the_bid_and_date_of_one_pair_when_successful(
        ClientInterface $client,
        Response $response,
        RequestInterface $request,
        CurrencyPairInterface $pair
    )
    {
        $uri = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=USD';
        $body = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_1.xml');

        $client->get(Argument::exact($uri))->willReturn($request);
        $client->send(array($request))->willReturn(array($response));
        $response->xml()->willReturn(new \SimpleXMLElement($body));

        $pair->getBaseCurrency()->willReturn('EUR');
        $pair->getQuoteCurrency()->willReturn('USD');
        $pair->setRate(Argument::exact('1.3608'))->shouldBeCalled();
        $pair->setDate(Argument::any())->shouldBeCalled();

        $this->beConstructedWith($client);
        $this->quote(array($pair));
    }

    function it_sets_the_bid_and_date_of_three_pairs_when_successful(
        ClientInterface $client,
        Response $responseOne,
        Response $responseTwo,
        Response $responseThree,
        RequestInterface $requestOne,
        RequestInterface $requestTwo,
        RequestInterface $requestThree,
        CurrencyPairInterface $pairOne,
        CurrencyPairInterface $pairTwo,
        CurrencyPairInterface $pairThree
    )
    {
        $pairOne->getBaseCurrency()->willReturn('EUR');
        $pairOne->getQuoteCurrency()->willReturn('USD');
        $pairOne->setRate(Argument::exact('1.3608'))->shouldBeCalled();
        $pairOne->setDate(Argument::any())->shouldBeCalled();

        $uriOne = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=USD';
        $bodyOne = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_1.xml');

        $client->get(Argument::exact($uriOne))->willReturn($requestOne);
        $responseOne->xml()->willReturn(new \SimpleXMLElement($bodyOne));

        $pairTwo->getBaseCurrency()->willReturn('EUR');
        $pairTwo->getQuoteCurrency()->willReturn('BOB');
        $pairTwo->setRate(Argument::exact('2.5000'))->shouldBeCalled();
        $pairTwo->setDate(Argument::any())->shouldBeCalled();

        $uriTwo = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=EUR&ToCurrency=BOB';
        $bodyTwo = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_2.xml');

        $client->get(Argument::exact($uriTwo))->willReturn($requestTwo);
        $responseTwo->xml()->willReturn(new \SimpleXMLElement($bodyTwo));

        $pairThree->getBaseCurrency()->willReturn('USD');
        $pairThree->getQuoteCurrency()->willReturn('BOB');
        $pairThree->setRate(Argument::exact('3.5000'))->shouldBeCalled();
        $pairThree->setDate(Argument::any())->shouldBeCalled();

        $uriThree = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=USD&ToCurrency=BOB';
        $bodyThree = file_get_contents(__DIR__ . '/../../Fixtures/Provider/WebserviceX/success_3.xml');

        $client->get(Argument::exact($uriThree))->willReturn($requestThree);
        $responseThree->xml()->willReturn(new \SimpleXMLElement($bodyThree));

        $client->send(array($requestOne, $requestTwo, $requestThree))->willReturn(array($responseOne, $responseTwo, $responseThree));

        $this->beConstructedWith($client);
        $this->quote(array($pairOne, $pairTwo, $pairThree));
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
