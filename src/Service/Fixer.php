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

use Swap\Contract\CurrencyPair;
use Swap\Contract\ExchangeRateQuery;
use Swap\Contract\HistoricalExchangeRateQuery;
use Swap\Exception\Exception;
use Swap\ExchangeRate;
use Swap\StringUtil;

/**
 * Fixer provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Fixer extends HistoricalService
{
    const LATEST_URL = 'https://api.fixer.io/latest?base=%s';
    const HISTORICAL_URL = 'http://api.fixer.io/%s?base=%s';

    /**
     * {@inheritdoc}
     */
    protected function getLatest(ExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        $url = sprintf(self::LATEST_URL, $currencyPair->getBaseCurrency());

        return $this->createRate($url, $currencyPair);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHistorical(HistoricalExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        $url = sprintf(
            self::HISTORICAL_URL,
            $exchangeQuery->getDate()->format('Y-m-d'),
            $currencyPair->getBaseCurrency()
        );

        return $this->createRate($url, $currencyPair);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeRateQuery $exchangeQuery)
    {
        return true;
    }

    /**
     * Creates a rate.
     *
     * @param string       $url
     * @param CurrencyPair $currencyPair
     *
     * @return ExchangeRate|null
     *
     * @throws Exception
     */
    private function createRate($url, CurrencyPair $currencyPair)
    {
        $content = $this->request($url);
        $data = StringUtil::jsonToArray($content);

        if (isset($data['rates'][$currencyPair->getQuoteCurrency()])) {
            $date = new \DateTimeImmutable('2016-08-26');
            $rate = $data['rates'][$currencyPair->getQuoteCurrency()];

            return new ExchangeRate($rate, $date);
        }
    }
}
