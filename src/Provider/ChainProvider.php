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

use Swap\Exception\ChainProviderException;
use Swap\Exception\InternalException;
use Swap\ExchangeQueryInterface;
use Swap\ProviderInterface;

/**
 * A provider using other providers in a chain.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class ChainProvider implements ProviderInterface
{
    private $providers;

    /**
     * Creates a new chain provider.
     *
     * @param ProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $exceptions = [];

        foreach ($this->providers as $provider) {
            try {
                return $provider->fetchRate($exchangeQuery);
            } catch (\Exception $e) {
                if ($e instanceof InternalException) {
                    throw $e;
                }

                $exceptions[] = $e;
            }
        }

        throw new ChainProviderException($exceptions);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        foreach ($this->providers as $provider) {
            if (!$provider->support($exchangeQuery)) {
                return false;
            }
        }

        return true;
    }
}
