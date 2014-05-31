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

use Swap\Exception\UnsupportedCurrencyPairException;

/**
 * Google Finance provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class GoogleFinance extends AbstractMultiRequestsProvider
{
    const URI = 'http://google.com/finance/converter?a=1&from=%s&to=%s';

    /**
     * {@inheritdoc}
     */
    protected function prepareRequests(array $pairs)
    {
        $requests = array();

        foreach ($pairs as $pair) {
            $uri = sprintf(self::URI, $pair->getBaseCurrency(), $pair->getQuoteCurrency());
            $request = $this->client->get($uri);
            $requests[] = $request;
        }

        return $requests;
    }

    /**
     * {@inheritdoc}
     */
    protected function processResponses(array $responses, array $pairs)
    {
        $date = new \DateTime();

        foreach ($responses as $key => $response) {
            $pair = $pairs[$key];

            $html = $response->getBody(true);
            $document = new \DOMDocument();
            @$document->loadHTML($html);

            $xpath = new \DOMXPath($document);
            $nodes = $xpath->query('//span[@class="bld"]');

            if (0 === $nodes->length) {
                throw new UnsupportedCurrencyPairException($pair);
            }

            $content = $nodes->item(0)->textContent;
            $bid = strstr($content, ' ', true);

            if (!is_numeric($bid)) {
                throw new UnsupportedCurrencyPairException($pair);
            }

            $pair->setRate($bid);
            $pair->setDate($date);
        }
    }
}
