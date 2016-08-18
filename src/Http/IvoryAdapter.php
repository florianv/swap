<?php

namespace Swap\Http;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Ivory\HttpAdapter\AbstractHttpAdapter;
use Ivory\HttpAdapter\Message\InternalRequestInterface;
use Ivory\HttpAdapter\Normalizer\BodyNormalizer;

/**
 * @deprecated To be removed in 3.0. In 3.0 we use the HttpClient directly
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IvoryAdapter extends AbstractHttpAdapter
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient = null)
    {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        parent::__construct();
    }

    protected function sendInternalRequest(InternalRequestInterface $internalRequest)
    {
        $response = $this->httpClient->sendRequest($internalRequest);

        return $this->getConfiguration()->getMessageFactory()->createResponse(
            (int) $response->getStatusCode(),
            $response->getProtocolVersion(),
            $response->getHeaders(),
            BodyNormalizer::normalize(
                function () use ($response) {
                    return $response->getBody()->detach();
                },
                $internalRequest->getMethod()
            )
        );
    }

    public function getName()
    {
        return 'swap_httplug';
    }
}
