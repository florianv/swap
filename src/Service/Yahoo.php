<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Service;

use Swap\Contract\ExchangeRateQuery;
use Swap\Contract\HistoricalExchangeRateQuery;
use Swap\Exception\Exception;
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\ExchangeRate;
use Swap\StringUtil;

/**
 * YahooFinance provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Yahoo extends Service
{
    const URL = 'https://query.yahooapis.com/v1/public/yql?q=%s&env=store://datatables.org/alltableswithkeys&format=json';

    /**
     * {@inheritdoc}
     */
    public function get(ExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        $queryPairs = sprintf('"%s%s"', $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
        $query = sprintf('select * from yahoo.finance.xchange where pair in (%s)', $queryPairs);
        $url = sprintf(self::URL, urlencode($query));

        $content = $this->request($url);

        $json = StringUtil::jsonToArray($content);

        if (isset($json['error'])) {
            throw new Exception($json['error']['description']);
        }

        $data = $json['query']['results']['rate'];

        if ('0.00' === $data['Rate'] || 'N/A' === $data['Date']) {
            throw new UnsupportedCurrencyPairException($currencyPair);
        }

        $dateString = $data['Date'].' '.$data['Time'];
        $date = \DateTime::createFromFormat('m/d/Y H:ia', $dateString);

        return new ExchangeRate($data['Rate'], $date);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeRateQuery $exchangeQuery)
    {
        return !$exchangeQuery instanceof HistoricalExchangeRateQuery;
    }
}
