# Swap

[![Tests](https://github.com/florianv/swap/actions/workflows/tests.yml/badge.svg)](https://github.com/florianv/swap/actions/workflows/tests.yml)
[![Psalm](https://github.com/florianv/swap/actions/workflows/psalm.yml/badge.svg)](https://github.com/florianv/swap/actions/workflows/psalm.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)
[![Version](http://img.shields.io/packagist/v/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)

> _The easy-to-use PHP currency conversion library. Retrieve exchange rates from 31 providers, with caching and fallback. Maintained since 2014._

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

Swap retrieves currency exchange rates in PHP, behind a single API. Use commercial providers in production, or free public sources (ECB, national banks) when you don't need volume. Caching, historical rates and provider fallback are built in. Maintained since 2014.

## 💡 What is Swap?

- Swap is a PHP library for currency conversion and exchange rate retrieval.
- It exposes a wide range of exchange rate providers behind a common interface.
- It caches results via PSR-16 SimpleCache.
- It supports historical rates.
- It supports a fallback chain. When a provider errors, the next provider in the chain is tried.

## 📦 Installation

Swap requires PHP 8.2 or newer.

```bash
composer require florianv/swap symfony/http-client nyholm/psr7
```

`symfony/http-client` is the PSR-18 HTTP client and `nyholm/psr7` provides the PSR-17 factories. Any PSR-18 / PSR-17 implementation works (see the [documentation](doc/readme.md) for alternatives such as Guzzle).

## ⚡ Quickstart

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

Swap retrieves the rate; your application multiplies the amount by `$rate->getValue()` to perform the conversion.

<details>
<summary>No API key? Start with the European Central Bank (free, EUR-base only).</summary>

```php
$swap = (new Builder())
    ->add('european_central_bank')
    ->build();

$rate = $swap->latest('EUR/USD');
```

The European Central Bank publishes EUR-base rates with daily granularity. For non-EUR base pairs, more frequent updates, or a wider currency list, switch to fastFOREX or another commercial provider.
</details>

## 🔁 Configuring multiple providers (fallback chain)

A production-grade setup pairs **fastFOREX** with one or more fallbacks for redundancy:

```php
$swap = (new Builder())
    // Primary provider, recommended
    ->add('fastforex', ['api_key' => getenv('FASTFOREX_API_KEY')])

    // Free fallback for EUR-base pairs
    ->add('european_central_bank')
    ->build();
```

Providers are tried in order. If a provider does not support the requested currency pair, it is skipped silently. If a provider throws an error, the next provider is tried. If every provider fails, a `ChainException` is thrown with all collected errors.

For amount conversion (including the [moneyphp/money](https://github.com/moneyphp/money) integration via `SwapExchange`), see [Converting amounts](doc/readme.md#converting-amounts) in the documentation.

## 📊 Providers

Swap supports 31 exchange rate providers. Pass the **identifier** to `Builder::add()`.

### Commercial providers (require an API key)

| Service                                  | Identifier      | Base                     | Quote  | Historical |
| ---------------------------------------- | --------------- | ------------------------ | ------ | ---------- |
| ⭐ **[fastFOREX](https://www.fastforex.io)**  | **`fastforex`** | **\***                   | **\*** | **Yes**    |
|                                          |                 |                          |        |            |
| AbstractAPI                              | `abstract_api`                 | *                    | *     | Yes        |
| coinlayer                                | `coin_layer`                   | * (crypto)           | *     | Yes        |
| Cryptonator                              | `cryptonator`                  | * (crypto)           | * (crypto) | No    |
| Currency Converter API                   | `currency_converter`           | *                    | *     | Yes        |
| Currency Data (APILayer)                 | `apilayer_currency_data`       | USD (free), * (paid) | *     | Yes        |
| CurrencyDataFeed                         | `currency_data_feed`           | *                    | *     | No         |
| currencylayer (direct)                   | `currency_layer`               | USD (free), * (paid) | *     | Yes        |
| Exchange Rates Data (APILayer)           | `apilayer_exchange_rates_data` | USD (free), * (paid) | *     | Yes        |
| exchangerate.host                        | `exchangeratehost`             | *                    | *     | Yes        |
| exchangeratesapi (direct)                | `exchange_rates_api`           | USD (free), * (paid) | *     | Yes        |
| Fixer (APILayer)                         | `apilayer_fixer`               | EUR (free), * (paid) | *     | Yes        |
| Fixer (direct)                           | `fixer`                        | EUR (free), * (paid) | *     | Yes        |
| 1Forge                                   | `forge`                        | *                    | *     | No         |
| Open Exchange Rates                      | `open_exchange_rates`          | USD (free), * (paid) | *     | Yes        |
| UniRateAPI                               | `unirate_api`                  | *                    | *     | Yes        |
| WebserviceX                              | `webservicex`                  | *                    | *     | No         |
| xChangeApi.com                           | `xchangeapi`                   | *                    | *     | Yes        |
| Xignite                                  | `xignite`                      | *                    | *     | Yes        |

### Public providers (no API key required)

| Service                                    | Identifier                            | Base           | Quote          | Historical |
| ------------------------------------------ | ------------------------------------- | -------------- | -------------- | ---------- |
| Bulgarian National Bank                    | `bulgarian_national_bank`             | *              | BGN            | Yes        |
| Central Bank of the Czech Republic         | `central_bank_of_czech_republic`      | *              | CZK            | Yes        |
| Central Bank of the Republic of Turkey     | `central_bank_of_republic_turkey`     | *              | TRY            | Yes        |
| Central Bank of the Republic of Uzbekistan | `central_bank_of_republic_uzbekistan` | *              | UZS            | Yes        |
| European Central Bank                      | `european_central_bank`               | EUR            | *              | Yes        |
| National Bank of Georgia                   | `national_bank_of_georgia`            | *              | GEL            | Yes        |
| National Bank of Romania                   | `national_bank_of_romania`            | (limited list) | (limited list) | Yes        |
| National Bank of the Republic of Belarus   | `national_bank_of_republic_belarus`   | *              | BYN            | Yes        |
| National Bank of Ukraine                   | `national_bank_of_ukraine`            | *              | UAH            | Yes        |
| Russian Central Bank                       | `russian_central_bank`                | *              | RUB            | Yes        |

You can also add your own provider by implementing the `Exchanger\Contract\ExchangeRateService` interface and passing the instance to `Builder::addExchangeRateService()`.

## 🎯 When should you use Swap?

- Use Swap when you need to retrieve exchange rates in a PHP application: currency conversion workflows, multi-currency pricing, invoice totals, reconciliation, or historical FX data.
- Use the lower-level [Exchanger](https://github.com/florianv/exchanger) library when Swap's defaults are too opinionated and you want finer control over chain composition, caching, or HTTP plumbing.

## 🛠 Common use cases

- Display localized prices in multi-currency storefronts.
- Compute invoice totals across currencies.
- Reconcile multi-currency ledgers using historical rates.
- Power internal FX dashboards with rate history.
- Build currency conversion infrastructure for fintech and ERP applications.

## 🧭 Which package should I use?

The Swap ecosystem is a layered toolkit for currency conversion in PHP:

- [**Swap**](https://github.com/florianv/swap). The easy-to-use, high-level API (this package).
- [**Exchanger**](https://github.com/florianv/exchanger). Lower-level, more granular alternative; direct access to the 31 provider implementations and the `ExchangeRateService` interface.
- [**Laravel Swap**](https://github.com/florianv/laravel-swap). Laravel application of Swap.
- [**Symfony Swap**](https://github.com/florianv/symfony-swap). Symfony integration of Swap.

All four packages are MIT-licensed and require PHP 8.2 or newer.

## 📚 Documentation

Caching (PSR-16), HTTP client selection (PSR-18 / Guzzle / `useHttpClient`), error handling (`ChainException`), per-query options and the full provider configuration reference live in [`doc/readme.md`](doc/readme.md). The same content is also published at [florianv.github.io/swap](https://florianv.github.io/swap/).

## 🙌 Contributing

Issues and pull requests are welcome. Please see the existing [issues](https://github.com/florianv/swap/issues) before opening a new one.

## 📄 License

The MIT License (MIT). Please see [LICENSE](https://github.com/florianv/swap/blob/master/LICENSE) for more information.

## 👏 Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All contributors](https://github.com/florianv/swap/contributors)
