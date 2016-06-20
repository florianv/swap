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
 * Currency Layer provider.
 *
 * @author Pascal Hofmann <mail@pascalhofmann.de>
 */
class CurrencyLayerProvider extends AbstractProvider
{
    const FREE_URL = 'http://www.apilayer.net/api/live?access_key=%s&currencies=%s';
    const ENTERPRISE_URL = 'https://www.apilayer.net/api/live?access_key=%s&source=%s&currencies=%s';

    private $accessKey;
    private $enterprise;

    /**
     * Creates a new provider.
     *
     * @param HttpAdapterInterface $httpAdapter The HTTP client
     * @param string               $accessKey   The access key.
     * @param bool                 $enterprise  A flag to tell if it is in enterprise mode
     */
    public function __construct(HttpAdapterInterface $httpAdapter, $accessKey, $enterprise = false)
    {
        parent::__construct($httpAdapter);

        $this->accessKey = $accessKey;
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
            $url = sprintf(self::ENTERPRISE_URL, $this->accessKey, $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
        } else {
            $url = sprintf(self::FREE_URL, $this->accessKey, $currencyPair->getQuoteCurrency());
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
}
