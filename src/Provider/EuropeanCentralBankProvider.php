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
use Swap\Exception\UnsupportedDateException;
use Swap\ExchangeQueryInterface;
use Swap\HistoricalExchangeQueryInterface;
use Swap\Model\Rate;
use Swap\Util\StringUtil;

/**
 * European Central Bank provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class EuropeanCentralBankProvider extends AbstractProvider
{
    const DAILY_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    const HISTORICAL_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml';

    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ('EUR' !== $currencyPair->getBaseCurrency()) {
            throw new UnsupportedCurrencyPairException($currencyPair);
        }

        if ($exchangeQuery instanceof HistoricalExchangeQueryInterface && $rate = $this->fetchHistoricalRate($exchangeQuery)) {
            return $rate;
        } elseif ($rate = $this->fetchLatestRate($exchangeQuery)) {
            return $rate;
        }

        throw new UnsupportedCurrencyPairException($currencyPair);
    }

    /**
     * Fetches the latest rate.
     *
     * @param ExchangeQueryInterface $exchangeQuery
     *
     * @return Rate|null
     *
     * @throws UnsupportedCurrencyPairException
     */
    private function fetchLatestRate(ExchangeQueryInterface $exchangeQuery)
    {
        $content = $this->fetchContent(self::DAILY_URL);

        $element = StringUtil::xmlToElement($content);
        $element->registerXPathNamespace('xmlns', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');

        $quoteCurrency = $exchangeQuery->getCurrencyPair()->getQuoteCurrency();
        $elements = $element->xpath('//xmlns:Cube[@currency="'.$quoteCurrency.'"]/@rate');

        if (empty($elements)) {
            throw new UnsupportedCurrencyPairException($exchangeQuery->getCurrencyPair());
        }

        $date = new \DateTime((string) $element->xpath('//xmlns:Cube[@time]/@time')[0]);

        return new Rate((string) $elements[0]['rate'], $date);
    }

    /**
     * Fetches an historical rate.
     *
     * @param HistoricalExchangeQueryInterface $exchangeQuery
     *
     * @return Rate|null
     *
     * @throws UnsupportedCurrencyPairException
     * @throws UnsupportedDateException
     */
    private function fetchHistoricalRate(HistoricalExchangeQueryInterface $exchangeQuery)
    {
        $content = $this->fetchContent(self::HISTORICAL_URL);

        $element = StringUtil::xmlToElement($content);
        $element->registerXPathNamespace('xmlns', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');

        $formattedDate = $exchangeQuery->getDate()->format('Y-m-d');
        $quoteCurrency = $exchangeQuery->getCurrencyPair()->getQuoteCurrency();

        $elements = $element->xpath('//xmlns:Cube[@time="'.$formattedDate.'"]/xmlns:Cube[@currency="'.$quoteCurrency.'"]/@rate');

        if (empty($elements)) {
            if (empty($element->xpath('//xmlns:Cube[@time="'.$formattedDate.'"]'))) {
                throw new UnsupportedDateException($exchangeQuery->getDate());
            }

            throw new UnsupportedCurrencyPairException($exchangeQuery->getCurrencyPair());
        }

        return new Rate((string) $elements[0]['rate'], $exchangeQuery->getDate());
    }
}
