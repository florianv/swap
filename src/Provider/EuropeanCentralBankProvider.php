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
 * European Central Bank provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class EuropeanCentralBankProvider extends AbstractProvider
{
    const URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * {@inheritdoc}
     */
    public function fetchRate(CurrencyPair $currencyPair)
    {
        if ('EUR' !== $currencyPair->getBaseCurrency()) {
            throw new UnsupportedCurrencyPairException($currencyPair);
        }

        $content = $this->fetchContent(self::URL);

        $xmlElement = StringUtil::xmlToElement($content);
        $cube = $xmlElement->Cube->Cube;
        $cubeAttributes = $cube->attributes();
        $date = new \DateTime((string) $cubeAttributes['time']);

        foreach ($cube->Cube as $cube) {
            $cubeAttributes = $cube->attributes();
            $cubeQuoteCurrency = (string) $cubeAttributes['currency'];

            if ($cubeQuoteCurrency === $currencyPair->getQuoteCurrency()) {
                return new Rate((string) $cubeAttributes['rate'], $date);
            }
        }

        throw new UnsupportedCurrencyPairException($currencyPair);
    }
}
