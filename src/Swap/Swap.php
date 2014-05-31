<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap;

/**
 * Default implementation allowing to chain providers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Swap implements SwapInterface
{
    /**
     * The providers.
     *
     * @var \Swap\ProviderInterface[]
     */
    private $providers;

    /**
     * Creates a new Swap.
     *
     * @param \Swap\ProviderInterface[] The providers
     */
    public function __construct(array $providers = array())
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritdoc}
     */
    public function quote($pairs)
    {
        if (!is_array($pairs)) {
            $pairs = array($pairs);
        }

        $pairs = $this->processTrivialPairs($pairs);

        if (empty($pairs)) {
            return;
        }

        if (empty($this->providers)) {
            throw new \RuntimeException('No providers have been added.');
        }

        $pairsToQuote = $pairs;

        for ($i = 0; $count = count($this->providers), $i < $count; $i++) {
            try {
                $this->providers[$i]->quote($pairsToQuote);
            } catch (\Exception $e) {
                if ($i === $count - 1) {
                    throw $e;
                }
            }

            $newPairsToQuote = array();

            foreach ($pairsToQuote as $pair) {
                if (null === $pair->getRate()) {
                    $newPairsToQuote[] = $pair;
                }
            }

            $pairsToQuote = $newPairsToQuote;

            if (empty($pairsToQuote)) {
                return;
            }
        }
    }

    /**
     * Adds a provider.
     *
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider)
    {
        if (!$this->hasProvider($provider)) {
            $this->providers[] = $provider;
        }
    }

    /**
     * Tells if the provider has been added.
     *
     * @param ProviderInterface $provider
     *
     * @return boolean
     */
    public function hasProvider(ProviderInterface $provider)
    {
        return in_array($provider, $this->providers, true);
    }

    /**
     * Process the "trivial" pairs and return the ones needing
     * to be quoted by the underlying providers.
     *
     * @param \Swap\Model\CurrencyPairInterface[] $pairs
     *
     * @return \Swap\Model\CurrencyPairInterface[]
     */
    private function processTrivialPairs(array $pairs)
    {
        $pairsToQuote = array();

        foreach ($pairs as $pair) {
            if ($pair->getBaseCurrency() === $pair->getQuoteCurrency()) {
                $pair->setRate('1');
                $pair->setDate(new \DateTime());
            } else {
                $pairsToQuote[] = $pair;
            }
        }

        return $pairsToQuote;
    }
}
