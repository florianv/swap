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
use Guzzle\Http\Message\Response;

/**
 * Yahoo Finance provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class YahooFinance extends AbstractSingleRequestProvider
{
    const URI = 'https://query.yahooapis.com/v1/public/yql?q=%s&env=store://datatables.org/alltableswithkeys&format=json';

    /**
     * {@inheritoc}
     */
    protected function prepareRequestUri(array $pairs)
    {
        $queryPairs = array();

        foreach ($pairs as $pair) {
            $queryPairs[] = sprintf(
                '"%s%s"',
                $pair->getBaseCurrency(),
                $pair->getQuoteCurrency()
            );
        }

        $query = sprintf('select * from yahoo.finance.xchange where pair in (%s)', implode(',', $queryPairs));

        return sprintf(self::URI, urlencode($query));
    }

    /**
     * {@inheritdoc}
     */
    protected function processResponse(Response $response, array $pairs)
    {
        // Prepare an array of pairs indexed by their "id"
        $hashPairs = array();
        foreach ($pairs as $pair) {
            $hashPairs[$pair->getBaseCurrency().$pair->getQuoteCurrency()] = $pair;
        }

        // Process the response content
        $json = $response->json();
        $results = $json['query']['results']['rate'];

        if (1 === count($pairs)) {
            $results = array($results);
        }

        foreach ($results as $result) {
            $pair = $hashPairs[$result['id']];

            if ('0.00' === $result['Rate'] || 'N/A' === $result['Date']) {
                throw new UnsupportedCurrencyPairException($pair);
            }

            $dateString = $result['Date'].' '.$result['Time'];
            $date = \DateTime::createFromFormat('m/d/Y H:ia', $dateString);

            $pair->setRate($result['Rate']);
            $pair->setDate($date);
        }
    }
}
