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

use Guzzle\Http\Exception\MultiTransferException;
use Swap\Exception\QuotationException;

/**
 * Base class for providers sending multiple requests to quote multiple pairs.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractMultiRequestsProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function quote(array $pairs)
    {
        $requests = $this->prepareRequests($pairs);

        $responses = $this->sendRequests($requests);

        $this->processResponses($responses, $pairs);
    }

    /**
     * Prepares the requests from the pairs to quote.
     *
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     *
     * @return \Guzzle\Http\Message\RequestInterface[]
     */
    abstract protected function prepareRequests(array $pairs);

    /**
     * Processes the responses.
     *
     * @param \Guzzle\Http\Message\Response[]     $responses
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     */
    abstract protected function processResponses(array $responses, array $pairs);

    /**
     * Sends the requests.
     *
     * @param \Guzzle\Http\Message\RequestInterface[] $requests
     *
     * @return \Guzzle\Http\Message\Response[] The responses
     *
     * @throws QuotationException
     */
    private function sendRequests(array $requests)
    {
        try {
            /** @var \Guzzle\Http\Message\Response[] $responses */
            $responses = $this->client->send($requests);
        } catch (MultiTransferException $e) {
            $failedRequests = $e->getFailedRequests();

            if (!empty($failedRequests)) {
                $message = sprintf(
                    'The request failed with a "%s" status code.',
                    $failedRequests[0]->getResponse()->getStatusCode()
                );
            } else {
                $message = 'The operation failed.';
            }

            throw new QuotationException($message);
        } catch (\Exception $e) {
            throw new QuotationException(sprintf(
                'The request failed with message: "%s".',
                $e->getMessage()
            ));
        }

        return $responses;
    }
}
