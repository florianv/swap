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

namespace Swap\Tests\Fixtures;

use Exchanger\Contract\ExchangeRate as ExchangeRateContract;
use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\Contract\ExchangeRateService;
use Exchanger\ExchangeRate;

/**
 * A service built from its options, which returns a rate named after the "name" option
 * or fails when "fail" is true.
 */
final class NamedService implements ExchangeRateService
{
    public function __construct(
        private readonly string $name,
        private readonly bool $fail = false,
    ) {}

    #[\Override]
    public function getExchangeRate(ExchangeRateQuery $exchangeQuery): ExchangeRateContract
    {
        if ($this->fail) {
            throw new \RuntimeException(\sprintf('Service "%s" failed.', $this->name));
        }

        return new ExchangeRate($exchangeQuery->getCurrencyPair(), 1.0, new \DateTimeImmutable(), $this->name);
    }

    #[\Override]
    public function supportQuery(ExchangeRateQuery $exchangeQuery): bool
    {
        return true;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }
}
