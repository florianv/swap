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

use Swap\Contract\ExchangeRateQuery;
use Swap\Contract\ExchangeRateService;
use Swap\Exception\ChainException;
use Swap\Exception\InternalException;

/**
 * A provider using other providers in a chain.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Chain implements ExchangeRateService
{
    private $providers;

    /**
     * Creates a new chain provider.
     *
     * @param ExchangeRateService[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritdoc}
     */
    public function get(ExchangeRateQuery $exchangeQuery)
    {
        $exceptions = [];

        foreach ($this->providers as $provider) {
            try {
                return $provider->get($exchangeQuery);
            } catch (\Exception $e) {
                if ($e instanceof InternalException) {
                    throw $e;
                }

                $exceptions[] = $e;
            }
        }

        throw new ChainException($exceptions);
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeRateQuery $exchangeQuery)
    {
        foreach ($this->providers as $provider) {
            if (!$provider->support($exchangeQuery)) {
                return false;
            }
        }

        return true;
    }
}
