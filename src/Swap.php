<?php

declare(strict_types=1);

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap;

use Exchanger\Contract\ExchangeRateProvider;
use Exchanger\ExchangeRateQueryBuilder;
use Exchanger\Contract\ExchangeRate as ExchangeRateContract;
use Exchanger\Exception\Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException as CacheInvalidArgumentException;

/**
 * Swap is an easy to use facade to retrieve exchange rates from various services.
 * @api
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Swap
{
    /**
     * The exchange rate provider.
     *
     * @var ExchangeRateProvider
     */
    private $exchangeRateProvider;

    /**
     * Creates a new Swap.
     *
     * @param ExchangeRateProvider $exchangeRateProvider
     */
    public function __construct(ExchangeRateProvider $exchangeRateProvider)
    {
        $this->exchangeRateProvider = $exchangeRateProvider;
    }

    /**
     * Quotes a currency pair.
     *
     * @param string $currencyPair The currency pair like "EUR/USD"
     * @param array  $options      An array of query options
     *
     * @return ExchangeRateContract
     *
     * @throws \InvalidArgumentException     When the currency pair is not in the form "EUR/USD".
     * @throws Exception                     When no rate can be provided. The subclass tells why:
     *                                       UnsupportedCurrencyPairException, UnsupportedDateException,
     *                                       UnsupportedExchangeQueryException, ChainException or CacheException.
     *                                       A Swap made by Builder wraps services in a Chain, so failures of
     *                                       the services themselves arrive as one ChainException.
     * @throws ClientExceptionInterface      When the HTTP request to the provider fails.
     * @throws \RuntimeException             When the provider's response cannot be parsed, for services that
     *                                       don't wrap parse errors in Exception yet.
     * @throws CacheInvalidArgumentException When the PSR-16 cache rejects the cache key.
     */
    public function latest(string $currencyPair, array $options = []): ExchangeRateContract
    {
        return $this->quote($currencyPair, null, $options);
    }

    /**
     * Quotes a currency pair.
     *
     * @param string             $currencyPair The currency pair like "EUR/USD"
     * @param \DateTimeInterface $date         An optional date for historical rates
     * @param array              $options      An array of query options
     *
     * @return ExchangeRateContract
     *
     * @throws \InvalidArgumentException     When the currency pair is not in the form "EUR/USD".
     * @throws Exception                     When no rate can be provided. The subclass tells why:
     *                                       UnsupportedCurrencyPairException, UnsupportedDateException,
     *                                       UnsupportedExchangeQueryException, ChainException or CacheException.
     *                                       A Swap made by Builder wraps services in a Chain, so failures of
     *                                       the services themselves arrive as one ChainException.
     * @throws ClientExceptionInterface      When the HTTP request to the provider fails.
     * @throws \RuntimeException             When the provider's response cannot be parsed, for services that
     *                                       don't wrap parse errors in Exception yet.
     * @throws CacheInvalidArgumentException When the PSR-16 cache rejects the cache key.
     */
    public function historical(string $currencyPair, \DateTimeInterface $date, array $options = []): ExchangeRateContract
    {
        return $this->quote($currencyPair, $date, $options);
    }

    /**
     * Quotes a currency pair.
     *
     * @param string             $currencyPair The currency pair like "EUR/USD"
     * @param \DateTimeInterface $date         An optional date for historical rates
     * @param array              $options      An array of query options
     *
     * @return ExchangeRateContract
     */
    private function quote(string $currencyPair, ?\DateTimeInterface $date = null, array $options = []): ExchangeRateContract
    {
        $exchangeQueryBuilder = new ExchangeRateQueryBuilder($currencyPair);

        if (null !== $date) {
            $exchangeQueryBuilder->setDate($date);
        }

        /** @var mixed $value */
        foreach ($options as $name => $value) {
            $exchangeQueryBuilder->addOption((string) $name, $value);
        }

        $query = $exchangeQueryBuilder->build();

        return $this->exchangeRateProvider->getExchangeRate($query);
    }
}
