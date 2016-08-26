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

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;

/**
 * Helps building services.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class ServiceFactory
{
    /**
     * The client.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * The request factory.
     *
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @param HttpClient|null     $httpClient
     * @param RequestFactory|null $requestFactory
     * @param array               $options
     */
    public function __construct(HttpClient $httpClient = null, RequestFactory $requestFactory = null, array $options = [])
    {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Sets the http client.
     *
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Sets the request factory.
     *
     * @param RequestFactory $requestFactory
     */
    public function setRequestFactory(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * Creates a new service.
     *
     * @param string $serviceName
     * @param array  $args
     *
     * @return \Swap\Contract\ExchangeRateService
     */
    public function createService($serviceName, array $args = [])
    {
        $services = self::getServices();

        if (!isset($services[$serviceName])) {
            throw new \InvalidArgumentException(sprintf('The service "%s" is not registered.'));
        }

        $class = $services[$serviceName];

        if (is_subclass_of($class, Service::class)) {
            return new $class($this->httpClient, $this->requestFactory, $args);
        }

        $r = new \ReflectionClass($class);

        return $r->newInstanceArgs($args);
    }

    /**
     * Gets the available services.
     *
     * @return array
     */
    private static function getServices()
    {
        return [
            'central_bank_of_czech_republic' => CentralBankOfCzechRepublic::class,
            'central_bank_of_republic_turkey' => CentralBankOfRepublicTurkey::class,
            'chain' => Chain::class,
            'currencylayer' => CurrencyLayer::class,
            'european_central_bank' => EuropeanCentralBank::class,
            'fixer' => Fixer::class,
            'google' => Google::class,
            'national_bank_of_romania' => NationalBankOfRomania::class,
            'open_exchange_rates' => OpenExchangeRates::class,
            'array' => PhpArray::class,
            'webservicex' => WebserviceX::class,
            'xignite' => Xignite::class,
            'yahoo' => Yahoo::class,
        ];
    }
}
