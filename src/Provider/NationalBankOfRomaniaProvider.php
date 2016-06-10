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
use Swap\Model\CurrencyPair;
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
    public function fetchRate(CurrencyPair $currencyPair)
    {
        $content = $this->fetchContent(self::URL);

        $xmlElement = StringUtil::xmlToElement($content);

        $baseCurrency = (string) $xmlElement->Body->OrigCurrency;
        if ($baseCurrency !== $currencyPair->getBaseCurrency()) {
            throw new UnsupportedCurrencyPairException($currencyPair);
        }

        $cube = $xmlElement->Body->Cube;
        $cubeAttributes = $cube->attributes();
        $date = new \DateTime((string) $cubeAttributes['date']);

        foreach ($cube->Rate as $rate) {
            $rateAttributes = $rate->attributes();
            $rateQuoteCurrency = (string) $rateAttributes['currency'];

            if ($rateQuoteCurrency === $currencyPair->getQuoteCurrency()) {
                $rateValue = (!empty($rateAttributes['multiplier'])) ? (float) $rate / (int) $rateAttributes['multiplier'] : (float) $rate;

                return new Rate((string) $rateValue, $date);
            }
        }

        throw new UnsupportedCurrencyPairException($currencyPair);
    }
}
