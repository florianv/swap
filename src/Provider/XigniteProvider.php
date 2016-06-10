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
use Swap\Model\CurrencyPair;
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
     * Creates a new provider.
     *
     * @param HttpAdapterInterface $httpAdapter The HTTP adapter
     * @param string               $token       The application token
     */
    public function __construct(HttpAdapterInterface $httpAdapter, $token)
    {
        parent::__construct($httpAdapter);
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(CurrencyPair $currencyPair)
    {
        $url = sprintf(self::URL, $currencyPair->getBaseCurrency().$currencyPair->getQuoteCurrency(), $this->token);
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
}
