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
use Swap\ProviderInterface;

/**
 * Base class for providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractProvider implements ProviderInterface
{
    protected $httpAdapter;

    public function __construct(HttpAdapterInterface $httpAdapter)
    {
        $this->httpAdapter = $httpAdapter;
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
        return (string) $this->httpAdapter->get($url)->getBody();
    }
}
