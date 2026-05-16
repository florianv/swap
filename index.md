---
title: "Swap: PHP currency conversion library"
description: PHP currency conversion library for retrieving exchange rates from 30 providers, with caching and fallback. Maintained since 2014.
---

**Resilient currency conversion in PHP, with fallback, caching, and zero vendor lock-in.**

Most exchange rate APIs are a single point of failure. Swap lets you build resilient currency infrastructure on top of multiple providers.

> Used in production PHP applications since 2014.

<table>
   <tr>
      <td width="220" align="center">
         <a href="https://www.fastforex.io" target="_blank" rel="noopener">
            <img src="https://console.fastforex.io/img/fastforex/logo-bk-1k.svg" width="180px" alt="fastFOREX"/>
         </a>
      </td>
      <td>
         <strong>Sponsored by <a href="https://www.fastforex.io" target="_blank" rel="noopener">fastFOREX</a>.</strong> Real-time JSON API, 160+ currencies, 55+ years of history, 500+ cryptocurrencies. <strong>Free tier</strong>; paid plans from $18/month.
         <a href="https://www.fastforex.io" target="_blank" rel="noopener"><strong>→ Get a free fastFOREX API key</strong></a>
      </td>
   </tr>
</table>

Swap retrieves currency exchange rates in PHP, behind a single API. Use commercial providers in production, or free public sources (ECB, national banks) when you don't need volume. Caching, historical rates and provider fallback are built in.

## What is Swap?

- Swap is a PHP library for currency conversion and exchange rate retrieval.
- It exposes a wide range of exchange rate providers behind a common interface.
- It caches results via PSR-16 SimpleCache.
- It supports historical rates.
- It supports a fallback chain. When a provider errors, the next provider in the chain is tried.

## Installation

Swap requires PHP 8.2 or newer.

```bash
composer require florianv/swap symfony/http-client nyholm/psr7
```

## Quickstart

The recommended setup uses **[fastFOREX](https://www.fastforex.io)** (the project's sponsor) as the primary provider. [Grab a free key](https://www.fastforex.io) and you're ready.

```php
use Swap\Builder;

// Recommended: fastFOREX. Get a free API key at https://www.fastforex.io
$swap = (new Builder())
    ->add('fastforex', ['api_key' => getenv('FASTFOREX_API_KEY')])
    ->build();

// EUR → USD exchange rate
$rate = $swap->latest('EUR/USD');

$rate->getValue();                 // e.g. 1.0823 (a float)
$rate->getDate()->format('Y-m-d'); // e.g. 2026-04-29
$rate->getProviderName();          // 'fastforex'

// Convert an amount using the returned rate
$amountInEUR = 100.00;
$amountInUSD = $amountInEUR * $rate->getValue();
```

No API key? Start with the European Central Bank (free, EUR-base only):

```php
$swap = (new Builder())
    ->add('european_central_bank')
    ->build();
```

## Production setup (fallback chain)

A production-grade chain pairs **fastFOREX** with a fallback for redundancy:

```php
$swap = (new Builder())
    // Primary provider, recommended
    ->add('fastforex', ['api_key' => getenv('FASTFOREX_API_KEY')])

    // Free fallback for EUR-base pairs
    ->add('european_central_bank')
    ->build();
```

Providers are tried in order. If a provider does not support the requested currency pair, it is skipped. If a provider throws, the next is tried. If every provider fails, Swap raises a `ChainException`.

## Providers

Swap supports 30 exchange rate providers, ranging from commercial APIs to public central banks. The recommended starting point for new projects is **[fastFOREX](https://www.fastforex.io)**: a real-time JSON API covering 160+ fiat currencies and 500+ cryptocurrencies, with up to 55 years of history, sourced from trusted feeds including world banks.

The full providers table, identifiers and configuration options live in the [documentation](https://github.com/florianv/swap/blob/master/doc/readme.md#-provider-configuration).

## Ecosystem

- [Swap](https://github.com/florianv/swap): easy-to-use PHP currency conversion library (this package).
- [Exchanger](https://github.com/florianv/exchanger): lower-level, more granular alternative; direct access to provider implementations.
- [Laravel Swap](https://github.com/florianv/laravel-swap): Laravel integration of Swap.
- [Symfony Swap](https://github.com/florianv/symfony-swap): Symfony integration of Swap.

## Documentation & source

- **Source code, issues and pull requests**: [github.com/florianv/swap](https://github.com/florianv/swap)
- **Full documentation** (caching, HTTP client, provider configuration, custom services): [doc/readme.md](https://github.com/florianv/swap/blob/master/doc/readme.md)

---

_Swap is open to selected partnerships with exchange rate providers and financial infrastructure companies. For inquiries, contact the maintainer via [GitHub](https://github.com/florianv)._
