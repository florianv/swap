# Swap

[![Tests](https://github.com/florianv/swap/actions/workflows/tests.yml/badge.svg)](https://github.com/florianv/swap/actions/workflows/tests.yml)
[![Psalm](https://github.com/florianv/swap/actions/workflows/psalm.yml/badge.svg)](https://github.com/florianv/swap/actions/workflows/psalm.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)
[![Version](http://img.shields.io/packagist/v/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)

> _The easy-to-use PHP currency conversion library. Retrieve exchange rates from 30 providers, with caching and fallback. Maintained since 2014._

Swap is a mature PHP **currency conversion library** for retrieving and working with exchange rates. It provides a single, easy-to-use API on top of 30 exchange rate providers, ranging from public sources (the European Central Bank, several national banks, exchangerate.host) to commercial **exchange rate APIs** that require an API key. Caching, historical rates, and a fallback chain are built in. Used in real-world PHP applications since 2014.

## What is Swap?

- Swap is a PHP library for currency conversion and exchange rate retrieval.
- It supports 30 exchange rate providers behind a common interface.
- It caches results via PSR-16 SimpleCache.
- It supports historical rates.
- It supports a fallback chain. When a provider errors, the next provider in the chain is tried.

## When should you use Swap?

- Use Swap when you need to retrieve exchange rates in a PHP application: currency conversion workflows, multi-currency pricing, invoice totals, reconciliation, or historical FX data.
- Use the lower-level [Exchanger](https://github.com/florianv/exchanger) library when Swap's defaults are too opinionated and you want finer control over chain composition, caching, or HTTP plumbing.

## Why not call an exchange rate API directly?

You can. Here is what Swap does for you so you don't have to build it again:

- **Provider abstraction.** Swap one provider for another without rewriting application code; all 30 providers expose the same interface.
- **Fallback chain.** Try a primary provider first, fall back to others on error. Unsupported currency pairs are skipped silently.
- **Caching.** PSR-16 SimpleCache integration with per-query TTL and per-query disable.
- **Historical rates.** One method (`historical()`) regardless of the underlying endpoint shape.
- **Typed result objects.** `getValue()`, `getDate()`, `getCurrencyPair()`, `getProviderName()` are uniform across providers, so no per-provider response parsing in your application code.
- **HTTP plumbing.** PSR-18 / PSR-17 friendly, auto-discovered via `php-http/discovery`. Any compliant HTTP client (Symfony HTTP Client, Guzzle, etc.) works without custom wiring.

## Installation

Swap requires PHP 8.2 or newer.

```bash
composer require florianv/swap symfony/http-client nyholm/psr7
```

`symfony/http-client` is the PSR-18 HTTP client and `nyholm/psr7` provides the PSR-17 factories. Any PSR-18 / PSR-17 implementation works (see the [documentation](doc/readme.md) for alternatives such as Guzzle).

## Quickstart

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
$amountInEUR  = 100.00;
$amountInUSD  = $amountInEUR * $rate->getValue();

// Retrieve a historical rate
$past = $swap->historical('EUR/USD', new \DateTime('-15 days'));
```

Swap retrieves the rate; your application multiplies the amount by `$rate->getValue()` to perform the conversion.

## Configuring multiple providers (fallback chain)

```php
$swap = (new Builder())
    ->add('your_primary_provider', ['api_key' => 'YOUR_KEY']) // see Providers below
    ->add('your_fallback_provider', ['api_key' => 'YOUR_KEY'])
    ->add('european_central_bank') // free fallback for EUR-base pairs
    ->build();
```

Providers are tried in order. If a provider does not support the requested currency pair, it is skipped silently. If a provider throws an error, the next provider is tried. If every provider fails, a `ChainException` is thrown with all collected errors.

## Common use cases

- Display localized prices in multi-currency storefronts.
- Compute invoice totals across currencies.
- Reconcile multi-currency ledgers using historical rates.
- Power internal FX dashboards with rate history.
- Build currency conversion infrastructure for fintech and ERP applications.

## Which package should I use?

The Swap ecosystem is a layered toolkit for currency conversion in PHP:

- **Swap.** The easy-to-use, high-level API (this package).
- **Exchanger.** Lower-level, more granular alternative; direct access to the 30 provider implementations and the `ExchangeRateService` interface.
- **Laravel Swap.** Laravel application of Swap.
- **Symfony Swap.** Symfony integration of Swap.

All four packages are MIT-licensed and require PHP 8.2 or newer.

## Providers

Swap supports 30 exchange rate providers via [Exchanger](https://github.com/florianv/exchanger). Pass the **identifier** to `Builder::add()`.

### Public providers (no API key required)

| Service                                    | Identifier                            | Base           | Quote          | Historical |
| ------------------------------------------ | ------------------------------------- | -------------- | -------------- | ---------- |
| Bulgarian National Bank                    | `bulgarian_national_bank`             | *              | BGN            | Yes        |
| Central Bank of the Czech Republic         | `central_bank_of_czech_republic`      | *              | CZK            | Yes        |
| Central Bank of the Republic of Turkey     | `central_bank_of_republic_turkey`     | *              | TRY            | Yes        |
| Central Bank of the Republic of Uzbekistan | `central_bank_of_republic_uzbekistan` | *              | UZS            | Yes        |
| Cryptonator                                | `cryptonator`                         | * (crypto)     | * (crypto)     | No         |
| European Central Bank                      | `european_central_bank`               | EUR            | *              | Yes        |
| exchangerate.host                          | `exchangeratehost`                    | *              | *              | Yes        |
| National Bank of Georgia                   | `national_bank_of_georgia`            | *              | GEL            | Yes        |
| National Bank of Romania                   | `national_bank_of_romania`            | (limited list) | (limited list) | Yes        |
| National Bank of the Republic of Belarus   | `national_bank_of_republic_belarus`   | *              | BYN            | Yes        |
| National Bank of Ukraine                   | `national_bank_of_ukraine`            | *              | UAH            | Yes        |
| Russian Central Bank                       | `russian_central_bank`                | *              | RUB            | Yes        |
| WebserviceX                                | `webservicex`                         | *              | *              | No         |

### Commercial providers (require an API key)

| Service                         | Identifier                     | Base                 | Quote | Historical |
| ------------------------------- | ------------------------------ | -------------------- | ----- | ---------- |
| AbstractAPI                     | `abstract_api`                 | *                    | *     | Yes        |
| coinlayer                       | `coin_layer`                   | * (crypto)           | *     | Yes        |
| Currency Converter API          | `currency_converter`           | *                    | *     | Yes        |
| Currency Data (APILayer)        | `apilayer_currency_data`       | USD (free), * (paid) | *     | Yes        |
| CurrencyDataFeed                | `currency_data_feed`           | *                    | *     | No         |
| currencylayer (direct)          | `currency_layer`               | USD (free), * (paid) | *     | Yes        |
| Exchange Rates Data (APILayer)  | `apilayer_exchange_rates_data` | USD (free), * (paid) | *     | Yes        |
| exchangeratesapi (direct)       | `exchange_rates_api`           | USD (free), * (paid) | *     | Yes        |
| fastFOREX.io                    | `fastforex`                    | USD (free), * (paid) | *     | No         |
| Fixer (APILayer)                | `apilayer_fixer`               | EUR (free), * (paid) | *     | Yes        |
| Fixer (direct)                  | `fixer`                        | EUR (free), * (paid) | *     | Yes        |
| 1Forge                          | `forge`                        | *                    | *     | No         |
| Open Exchange Rates             | `open_exchange_rates`          | USD (free), * (paid) | *     | Yes        |
| xChangeApi.com                  | `xchangeapi`                   | *                    | *     | Yes        |
| Xignite                         | `xignite`                      | *                    | *     | Yes        |

You can also add your own provider by implementing the `Exchanger\Contract\ExchangeRateService` interface and passing the instance to `Builder::addExchangeRateService()`.

## Caching, HTTP client, and error handling

- **Caching.** Swap uses PSR-16 `SimpleCache`. Configure once on the builder:

  ```php
  $swap = (new Builder())->useSimpleCache($psr16Cache)->add('european_central_bank')->build();
  ```

  Disable caching for a single query: `$swap->latest('EUR/USD', ['cache' => false])`.
  Override the TTL for a single query: `$swap->latest('EUR/USD', ['cache_ttl' => 3600])`.

- **HTTP client.** Any PSR-18 client (`symfony/http-client`, `php-http/guzzle7-adapter`, etc.) is supported and auto-discovered via `php-http/discovery`. To pass an explicit instance, use `Builder::useHttpClient()`.

- **Errors.** When every configured provider has either skipped (unsupported pair) or thrown, Swap raises an `Exchanger\Exception\ChainException` containing all collected exceptions.

## Documentation

The full documentation is in [`doc/readme.md`](doc/readme.md), and is also published at [florianv.github.io/swap](https://florianv.github.io/swap/).

## Related packages

The Swap ecosystem:

- [**Swap**](https://github.com/florianv/swap): easy-to-use PHP currency conversion library.
- [**Exchanger**](https://github.com/florianv/exchanger): lower-level, more granular alternative; direct access to provider implementations.
- [**Laravel Swap**](https://github.com/florianv/laravel-swap): Laravel application of Swap.
- [**Symfony Swap**](https://github.com/florianv/symfony-swap): Symfony integration of Swap.

## Sponsorship

The Swap ecosystem is open to selected sponsorships from exchange rate API providers and financial infrastructure companies.

Sponsorship can include:

- Documentation visibility
- Integration examples
- Ecosystem-level visibility across Swap, Exchanger, Laravel Swap, and Symfony Swap

For inquiries, contact the maintainer via [GitHub](https://github.com/florianv).

## Contributing

Issues and pull requests are welcome. Please see the existing [issues](https://github.com/florianv/swap/issues) before opening a new one.

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/florianv/swap/blob/master/LICENSE) for more information.

## Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All contributors](https://github.com/florianv/swap/contributors)
