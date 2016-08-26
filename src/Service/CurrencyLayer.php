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
 * Currency Layer provider.
 *
 * @author Pascal Hofmann <mail@pascalhofmann.de>
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class CurrencyLayer extends HistoricalService
{
    const FREE_LATEST_URL = 'http://www.apilayer.net/api/live?access_key=%s&currencies=%s';
    const ENTERPRISE_LATEST_URL = 'https://www.apilayer.net/api/live?access_key=%s&source=%s&currencies=%s';
    const FREE_HISTORICAL_URL = 'http://apilayer.net/api/historical?access_key=%s&date=%s';
    const ENTERPRISE_HISTORICAL_URL = 'https://apilayer.net/api/historical?access_key=%s&date=%s&source=%s';

    /**
     * {@inheritdoc}
     */
    public function processOptions(array &$options)
    {
        if (!isset($options['access_key'])) {
            throw new \InvalidArgumentException('The "access_key" option must be provided.');
        }

        if (!isset($options['enterprise'])) {
            $options['enterprise'] = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getLatest(ExchangeRateQuery $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($this->options['enterprise']) {
            $url = sprintf(
                self::ENTERPRISE_LATEST_URL,
                $this->options['access_key'],
                $currencyPair->getBaseCurrency(),
                $currencyPair->getQuoteCurrency()
            );
        } else {
            $url = sprintf(
                self::FREE_LATEST_URL,
                $this->options['access_key'],
                $currencyPair->getQuoteCurrency()
            );
        }

        return $this->createRate($url, $currencyPair);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHistorical(HistoricalExchangeRateQuery $exchangeQuery)
    {
        if ($this->options['enterprise']) {
            $url = sprintf(
                self::ENTERPRISE_HISTORICAL_URL,
                $this->options['access_key'],
                $exchangeQuery->getDate()->format('Y-m-d'),
                $exchangeQuery->getCurrencyPair()->getBaseCurrency()
            );
        } else {
            $url = sprintf(
                self::FREE_HISTORICAL_URL,
                $this->options['access_key'],
                $exchangeQuery->getDate()->format('Y-m-d')
            );
        }

        return $this->createRate($url, $exchangeQuery->getCurrencyPair());
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeRateQuery $exchangeQuery)
    {
        return $this->options['enterprise'] || 'USD' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
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

        if (empty($data['success'])) {
            throw new Exception($data['error']['info']);
        }

        $date = new \DateTime();
        $date->setTimestamp($data['timestamp']);
        $hash = $currencyPair->getBaseCurrency().$currencyPair->getQuoteCurrency();

        if ($data['source'] === $currencyPair->getBaseCurrency() && isset($data['quotes'][$hash])) {
            return new ExchangeRate((string) $data['quotes'][$hash], $date);
        }
    }
}
