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

use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Swap\Exception\Exception;
use Swap\ExchangeQueryInterface;
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
     * @var string
     */
    private $token;

    /**
     * Creates a new provider.
     *
     * @param string         $token          The application token
     * @param HttpClient     $httpClient
     * @param RequestFactory $requestFactory
     */
    public function __construct($token, HttpClient $httpClient = null, RequestFactory $requestFactory = null)
    {
        parent::__construct($httpClient, $requestFactory);
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();

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
