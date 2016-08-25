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
use Swap\ExchangeQueryInterface;
use Swap\HistoricalExchangeQueryInterface;
use Swap\Model\Rate;
use Swap\Util\StringUtil;

/**
 * Currency Layer provider.
 *
 * @author Pascal Hofmann <mail@pascalhofmann.de>
 */
class CurrencyLayerProvider extends AbstractProvider
{
    const FREE_URL = 'http://www.apilayer.net/api/live?access_key=%s&currencies=%s';
    const ENTERPRISE_URL = 'https://www.apilayer.net/api/live?access_key=%s&source=%s&currencies=%s';

    /**
     * {@inheritdoc}
     */
    public function processOptions(array &$options)
    {
        if (!isset($options['access_key'])) {
            throw new \InvalidArgumentException('The "access_key" option must be provided.');
        }

        if (empty($options['enterprise'])) {
            $options['enterprise'] = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

        if ($this->options['enterprise']) {
            $url = sprintf(self::ENTERPRISE_URL, $this->options['access_key'], $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
        } else {
            $url = sprintf(self::FREE_URL, $this->options['access_key'], $currencyPair->getQuoteCurrency());
        }

        $content = $this->fetchContent($url);
        $data = StringUtil::jsonToArray($content);

        if (empty($data['success'])) {
            throw new Exception($data['error']['info']);
        }

        $date = new \DateTime();
        $date->setTimestamp($data['timestamp']);

        if ($data['source'] === $currencyPair->getBaseCurrency()
            && isset($data['quotes'][$currencyPair->getBaseCurrency().$currencyPair->getQuoteCurrency()])
        ) {
            return new Rate((string) $data['quotes'][$currencyPair->getBaseCurrency().$currencyPair->getQuoteCurrency()], $date);
        }

        throw new UnsupportedCurrencyPairException($currencyPair);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        return !$exchangeQuery instanceof HistoricalExchangeQueryInterface
        && ($this->options['enterprise'] || 'USD' === $exchangeQuery->getCurrencyPair()->getBaseCurrency());
    }
}
