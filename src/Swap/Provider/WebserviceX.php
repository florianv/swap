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
    protected function prepareRequests(array $pairs)
    {
        $requests = array();

        foreach ($pairs as $pair) {
            $url = sprintf(self::URL, $pair->getBaseCurrency(), $pair->getQuoteCurrency());
            $requests[] = $this->client->get($url);
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
            $xml = $response->xml();
            $rate = (string) $xml;

            $pair->setRate($rate);
            $pair->setDate($date);
        }
    }
}
