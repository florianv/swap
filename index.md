---
title: Swap – PHP currency conversion library
description: PHP currency conversion library for retrieving exchange rates from 30 providers, with caching and fallback. Maintained since 2014.
---

Swap is a mature PHP **currency conversion library** for retrieving and working with exchange rates. It provides a single, easy-to-use API on top of multiple exchange rate providers, ranging from public sources (the European Central Bank, several national banks, exchangerate.host) to commercial **exchange rate APIs** that require an API key. Caching, historical rates, and a fallback chain are built in. Used in real-world PHP applications since 2014.

## What is Swap?

- Swap is a PHP library for currency conversion and exchange rate retrieval.
- It exposes a wide range of exchange rate providers behind a common interface.
- It caches results via PSR-16 SimpleCache.
- It supports historical rates.
- It supports a fallback chain. When a provider errors, the next provider in the chain is tried.

## When should you use Swap?

- Use Swap when you need to retrieve exchange rates in a PHP application: currency conversion workflows, multi-currency pricing, invoice totals, reconciliation, or historical FX data.
- Use the lower-level [Exchanger](https://github.com/florianv/exchanger) library when Swap's defaults are too opinionated and you want finer control over chain composition, caching, or HTTP plumbing.

## Why not call an exchange rate API directly?

You can integrate a single exchange rate API directly in your application.

Swap is useful when you need more than a single provider:

- **Provider abstraction** — switch providers without rewriting your code
- **Fallback support** — if one provider fails, another can be used automatically
- **Unified interface** — all providers share the same API
- **Caching** — reduce API calls and improve performance
- **Flexibility** — combine public and commercial providers

For simple use cases, calling a single API may be enough.

Swap becomes valuable when you need reliability, flexibility, or long-term maintainability.

## Quickstart

Swap requires PHP 8.2 or newer.

Install via Composer:

```bash
composer require florianv/swap symfony/http-client nyholm/psr7
```

Use it:

```php
use Swap\Builder;

// Build Swap with the European Central Bank (free, no API key required).
$swap = (new Builder())
    ->add('european_central_bank')
    ->build();

// EUR → USD exchange rate
$rate = $swap->latest('EUR/USD');

$rate->getValue();                 // e.g. 1.0823 (a float)
$rate->getDate()->format('Y-m-d'); // e.g. 2026-04-29
$rate->getProviderName();          // 'european_central_bank'

// Convert an amount using the returned rate
$amountInEUR = 100.00;
$amountInUSD = $amountInEUR * $rate->getValue();

// Retrieve a historical rate
$past = $swap->historical('EUR/USD', new \DateTime('-15 days'));
```

Swap retrieves the rate; your application multiplies the amount by `$rate->getValue()` to perform the conversion.

## View on GitHub

Source code, full documentation, providers list, and issue tracker:

**[View on GitHub →](https://github.com/florianv/swap)**

## Related packages

- [Swap](https://github.com/florianv/swap) – easy-to-use PHP currency conversion library (this package).
- [Exchanger](https://github.com/florianv/exchanger) – lower-level, more granular alternative; direct access to provider implementations.
- [Laravel Swap](https://github.com/florianv/laravel-swap) – Laravel application of Swap.
- [Symfony Swap](https://github.com/florianv/symfony-swap) – Symfony integration of Swap.

## Documentation

The full documentation, including the providers table, caching options, and how to write your own provider, is in [doc/readme.md](https://github.com/florianv/swap/blob/master/doc/readme.md) on the GitHub repository.
