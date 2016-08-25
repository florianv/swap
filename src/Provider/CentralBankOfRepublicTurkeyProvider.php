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
use Swap\ExchangeQueryInterface;
use Swap\HistoricalExchangeQueryInterface;
use Swap\Model\Rate;
use Swap\Util\StringUtil;

/**
 * Central Bank of Republic of Turkey (CBRT) provider.
 *
 * @link http://tcmb.gov.tr
 *
 * @author Uğur Erkan <mail@ugurerkan.com>
 */
class CentralBankOfRepublicTurkeyProvider extends AbstractProvider
{
    const URL = 'http://www.tcmb.gov.tr/kurlar/today.xml';

    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();
        $content = $this->fetchContent(self::URL);

        $element = StringUtil::xmlToElement($content);

        $date = new \DateTime((string) $element->xpath('//Tarih_Date/@Date')[0]);
        $elements = $element->xpath('//Currency[@CurrencyCode="'.$currencyPair->getBaseCurrency().'"]/ForexSelling');

        if (!empty($elements)) {
            return new Rate((string) $elements[0], $date);
        }

        throw new UnsupportedCurrencyPairException($currencyPair);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        return !$exchangeQuery instanceof HistoricalExchangeQueryInterface
        && 'TRY' === $exchangeQuery->getCurrencyPair()->getQuoteCurrency();
    }
}
