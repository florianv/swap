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

use Swap\Exception\Exception;
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Util\StringUtil;

/**
 * YahooFinance provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class YahooFinanceProvider extends AbstractProvider
{
    const URL = 'https://query.yahooapis.com/v1/public/yql?q=%s&env=store://datatables.org/alltableswithkeys&format=json';

    /**
     * {@inheritdoc}
     */
    public function fetchRate(CurrencyPair $currencyPair)
    {
        $queryPairs = sprintf('"%s%s"', $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
        $query = sprintf('select * from yahoo.finance.xchange where pair in (%s)', $queryPairs);
        $url = sprintf(self::URL, urlencode($query));

        $content = $this->fetchContent($url);

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

        return new Rate($data['Rate'], $date);
    }
}
