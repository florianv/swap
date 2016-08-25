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
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;
use Swap\ProviderInterface;

/**
 * Base class for providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractProvider implements ProviderInterface
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
     * The options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * @param HttpClient|null     $httpClient
     * @param RequestFactory|null $requestFactory
     * @param array               $options
     */
    public function __construct(HttpClient $httpClient = null, RequestFactory $requestFactory = null, array $options = [])
    {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: MessageFactoryDiscovery::find();

        $this->options = $this->processOptions($options);
    }

    /**
     * Processes the provider options.
     *
     * @param array $options
     *
     * @return array
     */
    public function processOptions(array $options)
    {
        return $options;
    }

    /**
     * Fetches the content of the given url.
     *
     * @param string $url
     *
     * @return string
     */
    protected function fetchContent($url)
    {
        $request = $this->requestFactory->createRequest('GET', $url);

        return $this->httpClient->sendRequest($request)->getBody()->__toString();
    }
}
