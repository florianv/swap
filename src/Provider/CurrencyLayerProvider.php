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
use Swap\Model\CurrencyPairInterface;
use Swap\Model\Rate;
use Swap\Util\StringUtil;

/**
 * Currency Layer provider.
 *
 * @author Pascal Hofmann <mail@pascalhofmann.de>
 */
class CurrencyLayerProvider extends AbstractHistoricalProvider
{
    const FREE_LATEST_URL = 'http://www.apilayer.net/api/live?access_key=%s&currencies=%s';
    const ENTERPRISE_LATEST_URL = 'https://www.apilayer.net/api/live?access_key=%s&source=%s&currencies=%s';
    const FREE_HISTORICAL_URL = 'http://apilayer.net/api/historical?access_key=%s&date=%s';
    const ENTERPRISE_HISTORICAL_URL = 'http://apilayer.net/api/historical?access_key=%s&date=%s&source=%s';

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
    public function fetchLatestRate(ExchangeQueryInterface $exchangeQuery)
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
     * Fetches an historical rate.
     *
     * @param HistoricalExchangeQueryInterface $exchangeQuery
     *
     * @return Rate|null
     */
    protected function fetchHistoricalRate(HistoricalExchangeQueryInterface $exchangeQuery)
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
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        return $this->options['enterprise'] || 'USD' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
    }

    /**
     * Creates a rate.
     *
     * @param string                $url
     * @param CurrencyPairInterface $currencyPair
     *
     * @return Rate|null
     *
     * @throws Exception
     */
    private function createRate($url, CurrencyPairInterface $currencyPair)
    {
        $content = $this->fetchContent($url);
        $data = StringUtil::jsonToArray($content);

        if (empty($data['success'])) {
            throw new Exception($data['error']['info']);
        }

        $date = new \DateTime();
        $date->setTimestamp($data['timestamp']);
        $hash = $currencyPair->getBaseCurrency().$currencyPair->getQuoteCurrency();

        if ($data['source'] === $currencyPair->getBaseCurrency() && isset($data['quotes'][$hash])) {
            return new Rate((string) $data['quotes'][$hash], $date);
        }
    }
}
