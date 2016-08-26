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
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\ExchangeRate;
use Swap\StringUtil;

/**
 * National Bank of Romania provider.
 *
 * @author Mihai Zaharie <mihai@zaharie.ro>
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class NationalBankOfRomania extends Service
{
    const URL = 'http://www.bnr.ro/nbrfxrates.xml';

    /**
     * {@inheritdoc}
     */
    public function get(ExchangeRateQuery $exchangeQuery)
    {
        $content = $this->request(self::URL);

        $element = StringUtil::xmlToElement($content);
        $element->registerXPathNamespace('xmlns', 'http://www.bnr.ro/xsd');

        $currencyPair = $exchangeQuery->getCurrencyPair();
        $date = new \DateTime((string) $element->xpath('//xmlns:PublishingDate')[0]);
        $elements = $element->xpath('//xmlns:Rate[@currency="'.$currencyPair->getQuoteCurrency().'"]');

        if (empty($elements)) {
            throw new UnsupportedCurrencyPairException($currencyPair);
        }

        $element = $elements[0];
        $rate = (string) $element;
        $rateValue = (!empty($element['multiplier'])) ? $rate / (int) $element['multiplier'] : $rate;

        return new ExchangeRate((string) $rateValue, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeRateQuery $exchangeQuery)
    {
        return !$exchangeQuery instanceof HistoricalExchangeRateQuery
        && 'RON' === $exchangeQuery->getCurrencyPair()->getQuoteCurrency();
    }
}
