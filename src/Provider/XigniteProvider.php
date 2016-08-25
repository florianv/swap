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
 * Xignite provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class XigniteProvider extends AbstractHistoricalProvider
{
    const LATEST_URL = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=%s&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=%s';
    const HISTORICAL_URL = 'http://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetHistoricalRates?Symbols=%s&AsOfDate=%s&_Token=%s&FixingTime=&PriceType=Mid';

    /**
     * {@inheritdoc}
     */
    public function processOptions(array &$options)
    {
        if (!isset($options['token'])) {
            throw new \InvalidArgumentException('The "token" option must be provided.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchLatestRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        $url = sprintf(self::LATEST_URL, $currencyPair->getBaseCurrency().$currencyPair->getQuoteCurrency(), $this->options['token']);
        $content = $this->fetchContent($url);

        $json = StringUtil::jsonToArray($content);
        $data = $json[0];

        if ('Success' === $data['Outcome']) {
            $dateString = $data['Date'].' '.$data['Time'];

            return new Rate(
                (string) $data['Bid'],
                \DateTime::createFromFormat('m/d/Y H:i:s A', $dateString, new \DateTimeZone('UTC'))
            );
        }

        throw new Exception($data['Message']);
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchHistoricalRate(HistoricalExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();
        $symbol = $currencyPair->getBaseCurrency().$currencyPair->getQuoteCurrency();
        $url = sprintf(self::HISTORICAL_URL, $symbol, $exchangeQuery->getDate()->format('m/d/Y'), $this->options['token']);

        $content = $this->fetchContent($url);

        $json = StringUtil::jsonToArray($content);
        $data = $json[0];

        if ('Success' === $data['Outcome']) {
            return new Rate(
                (string) $data['Average'],
                \DateTime::createFromFormat('m/d/Y', $data['StartDate'], new \DateTimeZone('UTC'))
            );
        }

        throw new Exception($data['Message']);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        return true;
    }
}
