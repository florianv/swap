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
    protected function prepareUris(array $pairs)
    {
        $uris = array();

        foreach ($pairs as $pair) {
            $uris[] = sprintf(self::URI, $pair->getBaseCurrency(), $pair->getQuoteCurrency());
        }

        return $uris;
    }

    /**
     * {@inheritdoc}
     */
    protected function processResponses(array $bodies, array $pairs)
    {
        $date = new \DateTime();

        foreach ($bodies as $key => $body) {
            $pair = $pairs[$key];

            $document = new \DOMDocument();
            @$document->loadHTML($body);

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
