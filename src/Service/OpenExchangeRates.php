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
use Swap\ExchangeRate;
use Swap\StringUtil;

/**
 * Open Exchange Rates Service.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class OpenExchangeRates extends HistoricalService
{
    const FREE_LATEST_URL = 'https://openexchangerates.org/api/latest.json?app_id=%s';
    const ENTERPRISE_LATEST_URL = 'https://openexchangerates.org/api/latest.json?app_id=%s&base=%s&symbols=%s';
    const FREE_HISTORICAL_URL = 'https://openexchangerates.org/api/historical/%s.json?app_id=%s';
    const ENTERPRISE_HISTORICAL_URL = 'https://openexchangerates.org/api/historical/%s.json?app_id=%s&base=%s&symbols=%s';

    /**
     * {@inheritdoc}
     */
    public function processOptions(array &$options)
    {
        if (!isset($options['app_id'])) {
            throw new \InvalidArgumentException('The "app_id" option must be provided.');
        }

        if (!isset($options['enterprise'])) {
            $options['enterprise'] = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getLatestExchangeRate(ExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($this->options['enterprise']) {
            $url = sprintf(
                self::ENTERPRISE_LATEST_URL,
                $this->options['app_id'],
                $currencyPair->getBaseCurrency(),
                $currencyPair->getQuoteCurrency()
            );
        } else {
            $url = sprintf(self::FREE_LATEST_URL, $this->options['app_id']);
        }

        return $this->createRate($url, $exchangeQuery);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHistoricalExchangeRate(HistoricalExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($this->options['enterprise']) {
            $url = sprintf(
                self::ENTERPRISE_HISTORICAL_URL,
                $exchangeQuery->getDate()->format('Y-m-d'),
                $this->options['app_id'],
                $currencyPair->getBaseCurrency(),
                $currencyPair->getQuoteCurrency()
            );
        } else {
            $url = sprintf(
                self::FREE_HISTORICAL_URL,
                $exchangeQuery->getDate()->format('Y-m-d'),
                $this->options['app_id']
            );
        }

        return $this->createRate($url, $exchangeQuery);
    }

    /**
     * {@inheritdoc}
     */
    public function supportQuery(ExchangeRateQuery $exchangeQuery)
    {
        return $this->options['enterprise'] || 'USD' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
    }

    /**
     * Creates a rate.
     *
     * @param string            $url
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate|null
     *
     * @throws Exception
     */
    private function createRate($url, ExchangeRateQuery $exchangeQuery)
    {
        $content = $this->request($url);
        $data = StringUtil::jsonToArray($content);

        if (isset($data['error'])) {
            throw new Exception($data['description']);
        }

        $date = new \DateTime();
        $date->setTimestamp($data['timestamp']);
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($data['base'] === $currencyPair->getBaseCurrency()
            && isset($data['rates'][$currencyPair->getQuoteCurrency()])
        ) {
            return new ExchangeRate((string) $data['rates'][$currencyPair->getQuoteCurrency()], $date);
        }
    }
}
