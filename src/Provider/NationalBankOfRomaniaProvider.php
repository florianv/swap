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
 * National Bank of Romania provider.
 *
 * @author Mihai Zaharie <mihai@zaharie.ro>
 */
class NationalBankOfRomaniaProvider extends AbstractProvider
{
    const URL = 'http://www.bnr.ro/nbrfxrates.xml';

    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $content = $this->fetchContent(self::URL);

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

        return new Rate((string) $rateValue, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        return !$exchangeQuery instanceof HistoricalExchangeQueryInterface
        && 'RON' === $exchangeQuery->getCurrencyPair()->getQuoteCurrency();
    }
}
