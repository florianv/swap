<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Provider;

use Swap\Exception\UnsupportedBaseCurrencyException;
use Swap\Exception\UnsupportedCurrencyPairException;
use Guzzle\Http\Message\Response;

/**
 * European Central Bank provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class EuropeanCentralBank extends AbstractSingleRequestProvider
{
    const URI = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * {@inheritdoc}
     */
    protected function prepareRequestUri(array $pairs)
    {
        foreach ($pairs as $pair) {
            if ('EUR' !== $pair->getBaseCurrency()) {
                throw new UnsupportedBaseCurrencyException($pair->getBaseCurrency());
            }
        }

        return self::URI;
    }

    /**
     * {@inheritdoc}
     */
    protected function processResponse(Response $response, array $pairs)
    {
        // Prepare an array of pairs indexed by their quote currency
        $hashPairs = array();
        foreach ($pairs as $pair) {
            $hashPairs[$pair->getQuoteCurrency()] = $pair;
        }

        // Process the response content
        $xmlElement = $response->xml();

        $cube = $xmlElement->Cube->Cube;
        $cubeAttributes = current($cube->attributes());
        $date = new \DateTime($cubeAttributes['time']);

        foreach ($cube->Cube as $cube) {
            $cubeAttributes = current($cube->attributes());

            if (isset($hashPairs[$cubeAttributes['currency']])) {
                $pair = $hashPairs[$cubeAttributes['currency']];
                $pair->setRate($cubeAttributes['rate']);
                $pair->setDate($date);
            }
        }

        // The pairs that were not quoted are not supported by this provider
        foreach ($pairs as $pair) {
            if (null === $pair->getRate()) {
                throw new UnsupportedCurrencyPairException($pair);
            }
        }
    }
}
