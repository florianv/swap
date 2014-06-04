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
        $xmlElement = $response->xml();
        $cube = $xmlElement->Cube->Cube;
        $cubeAttributes = $cube->attributes();
        $date = new \DateTime((string) $cubeAttributes['time']);
        $quotedPairs = array();

        foreach ($cube->Cube as $cube) {
            $cubeAttributes = $cube->attributes();
            $cubeQuoteCurrency = (string) $cubeAttributes['currency'];
            $cubeRate = (string) $cubeAttributes['rate'];
            $quotedPairs[$cubeQuoteCurrency] = $cubeRate;
        }

        foreach ($pairs as $pair) {
            $quoteCurrency = $pair->getQuoteCurrency();

            if (isset($quotedPairs[$quoteCurrency])) {
                $pair->setRate($quotedPairs[$quoteCurrency]);
                $pair->setDate($date);
            } else {
                throw new UnsupportedCurrencyPairException($pair);
            }
        }
    }
}
