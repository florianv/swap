<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Provider;

use Guzzle\Http\Exception\BadResponseException;
use Swap\Exception\QuotationException;
use Guzzle\Http\Message\Response;

/**
 * Base class for providers sending a single request to quote multiple pairs.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractSingleRequestProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function quote(array $pairs)
    {
        $uri = $this->prepareRequestUri($pairs);

        $response = $this->sendRequest($uri);

        $this->processResponse($response, $pairs);
    }

    /**
     * Prepares the request uri.
     *
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     *
     * @return string
     */
    abstract protected function prepareRequestUri(array $pairs);

    /**
     * Processes the response.
     *
     * @param Response                            $response
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     */
    abstract protected function processResponse(Response $response, array $pairs);

    /**
     * Sends the request.
     *
     * @param string $uri The uri
     *
     * @return Response
     *
     * @throws QuotationException
     */
    private function sendRequest($uri)
    {
        $request = $this->client->get($uri);

        try {
            $response = $request->send();
        } catch (BadResponseException $e) {
            throw new QuotationException(sprintf(
                'The request failed with a "%s" status code.',
                $e->getResponse()->getStatusCode()
            ));
        } catch (\Exception $e) {
            throw new QuotationException(sprintf(
                'The request failed with message: "%s".',
                $e->getMessage()
            ));
        }

        return $response;
    }
}
