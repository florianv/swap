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

use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Util\StringUtil;

/**
 * WebserviceX provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class WebserviceXProvider extends AbstractProvider
{
    const URL = 'http://www.webservicex.net/currencyconvertor.asmx/ConversionRate?FromCurrency=%s&ToCurrency=%s';

    /**
     * {@inheritdoc}
     */
    public function fetchRate(CurrencyPair $currencyPair)
    {
        $url = sprintf(self::URL, $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
        $content = $this->fetchContent($url);

        return new Rate((string) StringUtil::xmlToElement($content), new \DateTime());
    }
}
