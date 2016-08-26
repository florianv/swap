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
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\Exception\UnsupportedDateException;
use Swap\ExchangeRate;
use Swap\StringUtil;

/**
 * European Central Bank provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class EuropeanCentralBank extends HistoricalService
{
    const DAILY_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    const HISTORICAL_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml';

    /**
     * {@inheritdoc}
     */
    protected function getLatest(ExchangeRateQuery $exchangeQuery)
    {
        $content = $this->request(self::DAILY_URL);

        $element = StringUtil::xmlToElement($content);
        $element->registerXPathNamespace('xmlns', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');

        $quoteCurrency = $exchangeQuery->getCurrencyPair()->getQuoteCurrency();
        $elements = $element->xpath('//xmlns:Cube[@currency="'.$quoteCurrency.'"]/@rate');

        if (empty($elements)) {
            throw new UnsupportedCurrencyPairException($exchangeQuery->getCurrencyPair());
        }

        $date = new \DateTime((string) $element->xpath('//xmlns:Cube[@time]/@time')[0]);

        return new ExchangeRate((string) $elements[0]['rate'], $date);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHistorical(HistoricalExchangeRateQuery $exchangeQuery)
    {
        $content = $this->request(self::HISTORICAL_URL);

        $element = StringUtil::xmlToElement($content);
        $element->registerXPathNamespace('xmlns', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');

        $formattedDate = $exchangeQuery->getDate()->format('Y-m-d');
        $quoteCurrency = $exchangeQuery->getCurrencyPair()->getQuoteCurrency();

        $elements = $element->xpath('//xmlns:Cube[@time="'.$formattedDate.'"]/xmlns:Cube[@currency="'.$quoteCurrency.'"]/@rate');

        if (empty($elements)) {
            if (empty($element->xpath('//xmlns:Cube[@time="'.$formattedDate.'"]'))) {
                throw new UnsupportedDateException($exchangeQuery->getDate(), $this);
            }

            throw new UnsupportedCurrencyPairException($exchangeQuery->getCurrencyPair());
        }

        return new ExchangeRate((string) $elements[0]['rate'], $exchangeQuery->getDate());
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeRateQuery $exchangeQuery)
    {
        return 'EUR' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
    }
}
