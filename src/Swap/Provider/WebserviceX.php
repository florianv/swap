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

use Swap\Util\StringUtil;

/**
 * WebserviceX provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class WebserviceX extends AbstractMultiRequestsProvider
{
    const URL = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=%s&ToCurrency=%s';

    /**
     * {@inheritdoc}
     */
    protected function prepareUris(array $pairs)
    {
        $uris = array();

        foreach ($pairs as $pair) {
            $uris[] = sprintf(self::URL, $pair->getBaseCurrency(), $pair->getQuoteCurrency());
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
            $xml = StringUtil::xmlToElement($body);
            $rate = (string) $xml;

            $pair->setRate($rate);
            $pair->setDate($date);
        }
    }
}
