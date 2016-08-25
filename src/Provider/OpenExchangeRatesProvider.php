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
use Swap\ExchangeQueryInterface;
use Swap\HistoricalExchangeQueryInterface;
use Swap\Model\Rate;
use Swap\Util\StringUtil;

/**
 * Open Exchange Rates provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class OpenExchangeRatesProvider extends AbstractHistoricalProvider
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

        if (empty($options['enterprise'])) {
            $options['enterprise'] = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchLatestRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($this->options['enterprise']) {
            $url = sprintf(self::ENTERPRISE_LATEST_URL, $this->options['app_id'], $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
        } else {
            $url = sprintf(self::FREE_LATEST_URL, $this->options['app_id']);
        }

        return $this->createRate($url, $exchangeQuery);
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchHistoricalRate(HistoricalExchangeQueryInterface $exchangeQuery)
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
            $url = sprintf(self::FREE_HISTORICAL_URL, $exchangeQuery->getDate()->format('Y-m-d'), $this->options['app_id']);
        }

        return $this->createRate($url, $exchangeQuery);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        return $this->options['enterprise'] || 'USD' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
    }

    /**
     * Creates a rate.
     *
     * @param string                 $url
     * @param ExchangeQueryInterface $exchangeQuery
     *
     * @return Rate|null
     *
     * @throws Exception
     */
    private function createRate($url, ExchangeQueryInterface $exchangeQuery)
    {
        $content = $this->fetchContent($url);
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
            return new Rate((string) $data['rates'][$currencyPair->getQuoteCurrency()], $date);
        }
    }
}
