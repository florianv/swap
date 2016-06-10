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

use Ivory\HttpAdapter\HttpAdapterInterface;
use Swap\Exception\Exception;
use Swap\Exception\UnsupportedCurrencyPairException;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Util\StringUtil;

/**
 * Open Exchange Rates provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class OpenExchangeRatesProvider extends AbstractProvider
{
    const FREE_URL = 'https://openexchangerates.org/api/latest.json?app_id=%s';
    const ENTERPRISE_URL = 'https://openexchangerates.org/api/latest.json?app_id=%s&base=%s&symbols=%s';

    private $appId;
    private $enterprise;

    /**
     * Creates a new provider.
     *
     * @param HttpAdapterInterface $httpAdapter The HTTP client
     * @param string               $appId       The application id.
     * @param bool                 $enterprise  A flag to tell if it is in enterprise mode
     */
    public function __construct(HttpAdapterInterface $httpAdapter, $appId, $enterprise = false)
    {
        parent::__construct($httpAdapter);

        $this->appId = $appId;
        $this->enterprise = $enterprise;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(CurrencyPair $currencyPair)
    {
        if (!$this->enterprise && 'USD' !== $currencyPair->getBaseCurrency()) {
            throw new UnsupportedCurrencyPairException($currencyPair);
        }

        if ($this->enterprise) {
            $url = sprintf(self::ENTERPRISE_URL, $this->appId, $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
        } else {
            $url = sprintf(self::FREE_URL, $this->appId);
        }

        $content = $this->fetchContent($url);
        $data = StringUtil::jsonToArray($content);

        if (isset($data['error'])) {
            throw new Exception($data['description']);
        }

        $date = new \DateTime();
        $date->setTimestamp($data['timestamp']);

        if ($data['base'] === $currencyPair->getBaseCurrency()
            && isset($data['rates'][$currencyPair->getQuoteCurrency()])
        ) {
            return new Rate((string) $data['rates'][$currencyPair->getQuoteCurrency()], $date);
        }

        throw new UnsupportedCurrencyPairException($currencyPair);
    }
}
