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
class XigniteProvider extends AbstractProvider
{
    const URL = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=%s&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=%s';

    /**
     * {@inheritdoc}
     */
    public function processOptions(array $options)
    {
        if (!isset($options['token'])) {
            throw new \InvalidArgumentException('The token option must be provided');
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        $url = sprintf(self::URL, $currencyPair->getBaseCurrency().$currencyPair->getQuoteCurrency(), $this->options['token']);
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
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        return !$exchangeQuery instanceof HistoricalExchangeQueryInterface;
    }
}
